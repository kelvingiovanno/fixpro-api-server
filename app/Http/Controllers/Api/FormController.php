<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Applicant;
use App\Models\AuthenticationCode;

use App\Services\ApiResponseService;
use App\Services\AreaConfigService;
use App\Services\EntryService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use Throwable;

class FormController extends Controller
{
    private ApiResponseService $apiResponseService;
    private EntryService $entryService;
    private AreaConfigService $areaConfigService;

    public function __construct (
        ApiResponseService $_apiResponseService, 
        EntryService $_entryService, 
        AreaConfigService $_areaConfigService
    ) {
        $this->apiResponseService = $_apiResponseService;
        $this->entryService = $_entryService;
        $this->areaConfigService = $_areaConfigService;
    }

    public function getForm()
    {
        try 
        {
            $form = $this->areaConfigService->getJoinForm();
            $nonceToken = $this->entryService->generateNonce();

            return $this->apiResponseService->ok([
                'form_fields' => $form,
                'nonce' => $nonceToken,
            ], 'Form fields and nonce token successfully retrieved');
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
        $form = $this->areaConfigService->getJoinForm();

        $userData = collect($_request->input('data'))
            ->pluck('field_value', 'field_label')
            ->toArray();

        $rules = [];

        foreach ($form as $field) {
            $rules[$field] = ['required'];
        }

        $validator = Validator::make($userData, $rules);

        if ($validator->fails()) {
            
            return $this->apiResponseService->unprocessableEntity(
                'Validation failed',
                $validator->errors()
            );
        }
        
        try 
        {
            $new_applicant = Applicant::create($userData);

            $this->areaConfigService->incrementPendingMemberCount();

            return $this->apiResponseService->created([
                'application_id' => $new_applicant->id,
            ], 'Application submitted successfully');
        } 
        catch (Throwable $e) 
        {
            Log::error('Error occurred while submitting the application', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('Failed to submit application', 500);
        }
    }

    public function check(Request $_request)
    {
        try 
        {
            $application_id = $_request->input('application_id');
    
            $authCode = AuthenticationCode::where('applicant_id', $application_id)->first();
    
            if (!$authCode) {
                return $this->apiResponseService->internalServerError('Authentication code not found');
            }
    
            return $this->apiResponseService->ok([
                "authentication_code" => $authCode->id,
            ], "Authentication code retrieved");
        } 
        catch (Throwable $e) 
        {
            Log::error('Error occurred while retrieving authentication code', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('Failed to retrieve authentication code', 500);
        }
    }
}