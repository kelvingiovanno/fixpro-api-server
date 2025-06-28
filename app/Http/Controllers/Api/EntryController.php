<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Exceptions\InvalidNonceException;
use App\Exceptions\InvalidReferralException;
use App\Exceptions\JoinAreaException;
use App\Exceptions\JoinFormValidationException;

use App\Services\ApiResponseService;
use App\Services\AreaService;
use App\Services\JoinAreaService;
use App\Services\JoinFormService;
use App\Services\ReferralCodeService;
use App\Services\NonceCodeService;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use Throwable;

use function Psy\debug;

class EntryController extends Controller
{
    public function __construct (
        protected ApiResponseService $apiResponseService, 
        protected ReferralCodeService $referralCodeService,
        protected NonceCodeService $nonceCodeService, 
        protected JoinFormService $joinFormService,
        protected JoinAreaService $joinAreaService,
        protected AreaService $areaService,
    ) {}

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

            $area_name = $this->areaService->get_name();
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
            Log::error('An error occurred while retrieving form data', [
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

            $applicant = $this->joinAreaService->request($form, $nonce);

            $response_data = [
                'application_id' => $applicant->id,
                'application_expiry_date' => $applicant->expires_on->format('Y-m-d\TH:i:sP'),
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
            Log::error('An error occurred while submitting the form', [
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

        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.application_id' => 'required|string|uuid'
        ],
        [
            'data.required' => 'The data field is required.',
            'data.array' => 'The data field must be an array.',
            'data.application_id.required' => 'The application ID is required.',
            'data.application_id.string' => 'The application ID must be a string.',
            'data.application_id.uuid' => 'The application ID must be a valid UUID.',
        ]);

        if($validator->fails())
        {
            return $this->apiResponseService->badRequest('Validation failed.', $validator->errors());
        }
    
        try 
        {    
            $application_id = $request['data']['application_id'];
            $authentication_code = $this->joinAreaService->check($application_id);
            
            $response_data = [
                'authentication_code' => $authentication_code->id,
            ];

            return $this->apiResponseService->ok($response_data, 'Your application has been approved. Use the authentication code to proceed.');
        } 
        catch (ModelNotFoundException)
        {
            return $this->apiResponseService->notFound('Applicant not found.');
        }
        catch (JoinAreaException $e)
        {
            return $this->apiResponseService->forbidden($e->getMessage());
        }
        catch (Throwable $e) 
        {
            Log::error('An error occurred while retrieving authentication code', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }
}