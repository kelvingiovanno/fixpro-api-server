<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Enums\ApplicantStatusEnum;
use App\Enums\AreaJoinPolicyEnum;
use App\Enums\MemberRoleEnum;
use App\Exceptions\InvalidNonceException;
use App\Exceptions\InvalidReferralException;
use App\Exceptions\JoinFormValidationException;
use App\Models\Applicant;
use App\Models\AuthenticationCode;
use App\Models\SystemSetting;
use App\Models\Member;

use App\Services\ApiResponseService;
use App\Services\JoinFormService;
use App\Services\JoinPolicyService;
use App\Services\ReferralCodeService;
use App\Services\NonceCodeService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use Throwable;

class EntryController extends Controller
{
    private ApiResponseService $apiResponseService;
    private ReferralCodeService $referralCodeService;
    private NonceCodeService $nonceCodeService;
    private JoinFormService $joinFormService;
    private JoinPolicyService $joinPolicyService;

    public function __construct (
        ApiResponseService $_apiResponseService, 
        ReferralCodeService $_referralCodeService,
        NonceCodeService $_nonceCodeService, 
        JoinFormService $_joinFormService,
        JoinPolicyService $_joinPolicyService,
    ) {
        $this->apiResponseService = $_apiResponseService;
        $this->referralCodeService = $_referralCodeService;
        $this->nonceCodeService = $_nonceCodeService;
        $this->joinFormService = $_joinFormService;
        $this->joinPolicyService = $_joinPolicyService;
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->query(), [
            'area_join_form_referral_tracking_identifier' => 'required|string',
        ], 
        [
            'area_join_form_referral_tracking_identifier.required' => 'Referral tracking identifier is required.',
            'area_join_form_referral_tracking_identifier.string' => 'Referral tracking identifier must be a valid string.',
        ]);

        if($validator->fails())
        {
            return $this->apiResponseService->badRequest('Validation failed.', $validator->errors());
        }        

        try 
        {
            $this->referralCodeService->check($request->query('area_join_form_referral_tracking_identifier'));

            $area_name = SystemSetting::get('area_name');
            $form_fields = $this->joinFormService->form();
            $nonce = $this->nonceCodeService->generate();

            $response_data = [
                'area_name' => $area_name,
                'form_fields' => $form_fields,
                'nonce' => $nonce,
            ];

            return $this->apiResponseService->ok($response_data, 'All the required fields and a nonce to be used for submission.');
        } 
        catch (InvalidReferralException $e)
        {
            return $this->apiResponseService->badRequest($e->getMessage());
        }
        catch (Throwable $e) 
        {
            Log::error('Error occurred while retrieving form data', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(), 
            ]);

            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(),
            [
                'area_join_form_submission_nonce' => 'required|string',
                'data' => 'required|array',
                'data.*.field_label' => 'required|string',
                'data.*.field_value' => 'required|string',
            ],
            [
                'area_join_form_submission_nonce.required' => 'Submission nonce is required.',
                'area_join_form_submission_nonce.string' => 'Submission nonce must be a valid string.',

                'data.required' => 'The data field is required.',
                'data.array' => 'The data field must be an array of field entries.',

                'data.*.field_label.required' => 'Each field must have a label.',
                'data.*.field_label.string' => 'The field label must be a string.',
             
                'data.*.field_value.required' => 'Each field must have a value.',
                'data.*.field_value.string' => 'The field value must be a string.',
            ]
        );

        if($validator->fails()) {
            return $this->apiResponseService->badRequest('Validator failed.', $validator->errors());
        }

        try 
        {
            $form = $request['data'];
            $nonce = $request->query('area_join_form_submission_nonce');
            
            $applicant = $this->joinPolicyService->request($form, $nonce);

            $response_data = [
                'application_id' => $applicant->id,
                'application_expiry_date' => $applicant->expires_on,
            ];

            return $this->apiResponseService->ok(
                $response_data, 
                'Successfully submitted an application. Use the following string to check periodically of your application status.'
            );
        } 
        catch (InvalidNonceException $e)
        {
            return $this->apiResponseService->forbidden($e->getMessage());
        }
        catch (JoinFormValidationException $e)
        {
            return $this->apiResponseService->badRequest($e->getMessage());
        }
        catch (Throwable $e) 
        {
            Log::error('Error occurred while submitting the form', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }
    
    public function check(Request $request)
    {
        $application_id = $request->input('data.application_id');
    
        if (!$application_id) {
            return $this->apiResponseService->badRequest('Application ID is required.');
        }
    
        if (!Str::isUuid($application_id)) {
            return $this->apiResponseService->badRequest('Application not found');
        }
    
        try 
        {    
            $applicant = Applicant::with('status')->find($application_id);
        
            if (!$applicant) {
                return $this->apiResponseService->badRequest('Application not found.');
            }
            
            if ($applicant->expires_on < now()) {
                return $this->apiResponseService->forbidden('Your application has expired.');
            }
            
            $statusId = $applicant->status->id;

            $first_member_login = SystemSetting::get('first_member_login');

            if(!$first_member_login)
            {
                $applicant->member()->update([
                    'role_id' => MemberRoleEnum::MANAGEMENT->id(),
                ]);

                SystemSetting::put('first_member_login', "1");
            }

            switch ($statusId) {
                case ApplicantStatusEnum::PENDING->id():
                    return $this->apiResponseService->ok(null, 'Your application is still pending.');
                
                case ApplicantStatusEnum::REJECTED->id():
                    return $this->apiResponseService->forbidden('Your application has been rejected.');
                
                case ApplicantStatusEnum::ACCEPTED->id():
                    $authCode = AuthenticationCode::create([
                        'application_id' => $application_id,
                    ]);
                    
                    return $this->apiResponseService->ok(
                        ['authentication_code' => $authCode->id],
                        'Your application has been approved. Use the authentication code to proceed.'
                    );
            
                default:
                    return $this->apiResponseService->internalServerError('Unknown application status.');
            }
        } 
        catch (Throwable $e) 
        {
            Log::error('Error occurred while retrieving authentication code', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }
}