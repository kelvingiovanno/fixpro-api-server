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

    public function submit(Request $_request)
    {
        $userData = $_request->input('form_data');
        $application_id = $this->entryService->generateApplicationId();

        $newUserData = array_merge($userData, ['application_id' => $application_id]);

        PendingApplication::create($newUserData);
        
        $data = [
            "application_id" => $application_id
        ];

        return $this->apiResponseService->created($data, 'Application submitted successfully');
    }

    public function check(Request $_request)
    {

        $user_id = $_request->user_id;

        $authentication_code = $this->entryService->generateAuthenticationCode($user_id);

        $data = [
            "authentication_code" => $authentication_code,
        ];

        return $this->apiResponseService->ok($data, "New authentication code generated");
    }
}
