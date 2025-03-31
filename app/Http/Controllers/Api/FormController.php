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

    public function requestForm() 
    {
        $form = UserData::getColumnNames();
        $nonceToken = $this->entryService->generateNonce();

        $data = [
            'form_fields' => $form,
            'nonce' => $nonceToken,
        ];
        
        return $this->apiResponseService->success($data, 'Code accepted');
    }

    public function submitForm(Request $_request)
    {
        $userData = $_request->input('form_data');        
        $application_id = $this->entryService->generateApplicationId();

        $newUserData = array_merge($userData, ['application_id' => $application_id]);

        PendingApplication::create($newUserData);

        return $this->apiResponseService->success($application_id,'Code accepted');
    }

    public function check(Request $_request)
    {
        $application_id = $_request->query('application_id');

        $isValid = $this->entryService->checkApplicationId($application_id);

        if($isValid)
        {
            return $this->apiResponseService->success("nice");
        }
        
        return $this->apiResponseService->success("no nice");
        
    }
}
