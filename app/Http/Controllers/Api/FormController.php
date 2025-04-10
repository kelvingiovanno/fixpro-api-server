<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Services\ApiResponseService;
use App\Services\EntryService;

use App\Models\UserData;
use App\Models\PendingApplication;

use Illuminate\Http\Request;

class FormController extends Controller
{

    private ApiResponseService $apiResponseService;
    private EntryService $entryService;

    public function __construct(ApiResponseService $_apiResponseService, EntryService $_entryService)
    {
        $this->apiResponseService = $_apiResponseService;
        $this->entryService = $_entryService;
    }

    public function request() 
    {
        $form = UserData::getColumnNames();
        $nonceToken = $this->entryService->generateNonce();

        $data = [
            'form_fields' => $form,
            'nonce' => $nonceToken,
        ];
        
        return $this->apiResponseService->ok($data, 'Form fields and nonce token successfully retrieved');
    }

    public function submit(Request $request)
    {
        $fieldData = $request->input('data');
        $encryption_key = $request->input('encryption_key');
    
        $userData = collect($fieldData)->mapWithKeys(function ($item) {
            return [$item['field_label'] => $item['field_value']];
        })->toArray();
    
        $application_id = $this->entryService->generateApplicationId();
        $newUserData = array_merge($userData, ['application_id' => $application_id, 'encryption_key' => $encryption_key]);
    
        PendingApplication::create($newUserData);
    
        return $this->apiResponseService->created([
            'application_id' => $application_id,
        ], 'Application submitted successfully');
    }
    

    public function check(Request $request)
    {
        $user_id = $request->input('user_id');
    
        $authentication_code = $this->entryService->generateAuthenticationCode($user_id);
    
        $data = [
            "authentication_code" => $authentication_code,
        ];
    
        return $this->apiResponseService->ok($data, "New authentication code generated");
    }
}
