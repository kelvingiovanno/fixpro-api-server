<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Enums\ApplicantStatusEnum;

use App\Models\Applicant;
use App\Models\AuthenticationCode;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\UserData;

use App\Services\ApiResponseService;
use App\Services\ReferralCodeService;
use App\Services\NonceCodeService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use Throwable;

class FormController extends Controller
{
    private ApiResponseService $apiResponseService;
    private ReferralCodeService $referralCodeService;
    private NonceCodeService $nonceCodeService;

    public function __construct (
        ApiResponseService $_apiResponseService, 
        ReferralCodeService $_referralCodeService,
        NonceCodeService $_nonceCodeService, 
    ) {
        $this->apiResponseService = $_apiResponseService;
        $this->referralCodeService = $_referralCodeService;
        $this->nonceCodeService = $_nonceCodeService;
    }

    public function getForm(Request $_request)
    {
        $referralCode = $_request->query('ref');

        if (!$referralCode) {
            return $this->apiResponseService->badRequest('Referral code is required.');
        }

        if (!$this->referralCodeService->checkReferral($referralCode)) {
            return $this->apiResponseService->forbidden('The provided referral code is invalid or has expired.');
        }

        try 
        {
            $nonceToken = $this->nonceCodeService->generateNonce();
            $area_name = SystemSetting::get('area_name');
    
            $form = json_decode(SystemSetting::get('area_join_form'),true);
            $form_fields = collect($form)->map(function ($field) {
                return ['field_label' => ucfirst(str_replace('_', ' ', $field))];
            })->values();

            return $this->apiResponseService->ok([
                'area_name' => $area_name,
                'form_fields' => $form_fields,
                'nonce' => $nonceToken,
            ], 'All the required fields and a nonce to be used for submission.');
        } 
        catch (Throwable $e) 
        {
            Log::error('Error occurred while retrieving form data', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(), 
            ]);

            return $this->apiResponseService->internalServerError('Failed to get form data', 500);
        }
    }

    public function submit(Request $_request)
    {
        $nonce_code = $_request->query('nonce');

        if (!$nonce_code) {
            return $this->apiResponseService->badRequest('Nonce code is required.');
        }

        if (!$this->nonceCodeService->checkNonce($nonce_code)) {
            return $this->apiResponseService->forbidden('The provided referral code is invalid or has expired.');
        }

        $join_policy = SystemSetting::get('area_join_policy');

        if($join_policy == 'closed') {
            $this->nonceCodeService->deleteNonce($nonce_code);
            return $this->apiResponseService->forbidden('');
        }

        $form_labels = json_decode(SystemSetting::get('area_join_form'),true);

        $formattedFormLabels = array_map(function ($form_label) {
            return ucwords(str_replace('_', ' ', $form_label));
        }, $form_labels);

        $form_data = collect($_request->input('data'))
            ->pluck('field_value', 'field_label')
            ->toArray();

        $rules = [];
        foreach ($formattedFormLabels as $label) {
            $rules[$label] = ['required'];
        }

        $validator = Validator::make($form_data, $rules);

        if ($validator->fails()) {
            return $this->apiResponseService->badRequest(
                'Validation failed',
                $validator->errors()
            );
        }

        $normalizedData = [];
        foreach ($form_data as $label => $value) {
            $normalizedKey = strtolower(str_replace(' ', '_', $label));
            $normalizedData[$normalizedKey] = $value;
        }

        try 
        {
            $applicant = Applicant::create(array_merge($normalizedData, ['status_id' => 1]));
            
            if($join_policy == 'open')
            {
                $user = User::create([
                    'name' => $applicant->name,
                ]);

                $applicantData = $applicant->toArray();
                unset($applicantData['name'], $applicantData['status_id'], $applicantData['id'], $applicantData['expires_at']);
                
                UserData::create(array_merge($applicantData, [
                    'user_id' => $user->id,
                ]));

                $applicant->update(['is_accepted' => true]);

                AuthenticationCode::create([
                    'applicant_id' => $applicant->id,
                    'user_id' => $user->id,
                ]);
            }

            $response_data = [
                'application_id' => $applicant->id,
                'application_expiry_date' => $applicant->expires_at,
            ];
            
            $this->nonceCodeService->deleteNonce($nonce_code);
            
            return $this->apiResponseService->ok(
                $response_data, 
                'Successfully submitted an application. Use the following string to check periodically of your application status.'
            );
        } 
        catch (Throwable $e) 
        {
            Log::error('Error occurred while submitting the application', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('Failed to submit application');
        }
    }

    public function check(Request $request)
    {
        $applicationId = $request->input('application_id');
    
        if (!$applicationId) {
            return $this->apiResponseService->badRequest('Application ID is required.');
        }
    
        if (!Str::isUuid($applicationId)) {
            return $this->apiResponseService->badRequest('Application not found');
        }
    
        try 
        {    
            $applicant = Applicant::with('status')->find($applicationId);
        
            if (!$applicant) {
                return $this->apiResponseService->badRequest('Application not found.');
            }
            
            if ($applicant->expires_at < now()) {
                return $this->apiResponseService->forbidden('Your application has expired.');
            }
            
            $statusId = $applicant->status->id;

            switch ($statusId) {
                case ApplicantStatusEnum::PENDING->id():
                    return $this->apiResponseService->ok(null, 'Your application is still pending.');
                
                case ApplicantStatusEnum::REJECTED->id():
                    return $this->apiResponseService->forbidden('Your application has been rejected.');
                
                case ApplicantStatusEnum::ACCEPTED->id():
                    $authCode = AuthenticationCode::where('applicant_id', $applicationId)->first();
                    if (!$authCode) {
                        return $this->apiResponseService->notFound('Authentication code not found for this applicant.');
                    }
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
    
            return $this->apiResponseService->internalServerError('Failed to process your application status.');
        }
    }
}