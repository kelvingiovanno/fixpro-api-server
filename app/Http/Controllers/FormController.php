<?php

namespace App\Http\Controllers;

use App\Services\ReferralCodeService;
use App\Services\ApiResponseService;

use App\Models\UserData;
use App\Models\User;

use Illuminate\Http\Request;

class FormController extends Controller
{

    protected ReferralCodeService $referralCodeService;
    protected ApiResponseService $apiResponseService;

    public function __construct(ReferralCodeService $_referralCodeService, ApiResponseService $_apiResponseService)
    {
        $this->referralCodeService = $_referralCodeService;
        $this->apiResponseService = $_apiResponseService;
    }

    public function requestForm() 
    {
        $data = UserData::getColumnNames();
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
