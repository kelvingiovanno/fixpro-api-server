<?php

namespace App\Http\Controllers;

use App\Services\ApiResponseService;
use App\Services\NonceService;

use App\Models\UserData;
use App\Models\User;

use Illuminate\Http\Request;

class FormController extends Controller
{

    private ApiResponseService $apiResponseService;
    private NonceService $nonceService;

    public function __construct(ApiResponseService $_apiResponseService, NonceService $_nonceService)
    {
        $this->apiResponseService = $_apiResponseService;
        $this->nonceService = $_nonceService;
    }

    public function requestForm() 
    {
        $form = UserData::getColumnNames();
        $nonceToken = $this->nonceService->generateNonce();

        $data = [
            'form' => $form,
            'nonce_token' => $nonceToken,
        ];
        
        return $this->apiResponseService->success($data, 'Code accepted', 200);
    }

    public function submitForm(Request $_request)
    {
        $newUserData = $_request->input('user_data');
        $newUser = $_request->input('user');

        $newUserCreated = User::create($newUser);
        $newUserId = $newUserCreated->id;

        $newUserData['user_id'] = $newUserId;

        UserData::create($newUserData);

        

        return $this->apiResponseService->success('Code accepted', 200);
    }
}
