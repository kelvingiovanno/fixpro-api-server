<?php

namespace App\Http\Controllers;

use App\Services\ReferralCodeService;
use App\Services\ApiResponseService;

use App\Models\UserData;

use Illuminate\Http\Request;

class JoinAreaController extends Controller
{

    protected ReferralCodeService $referralCodeService;
    protected ApiResponseService $apiResponseService;

    public function __construct(ReferralCodeService $_referralCodeService, ApiResponseService $_apiResponseService)
    {
        $this->referralCodeService = $_referralCodeService;
        $this->apiResponseService = $_apiResponseService;
    }

    public function index(Request $_request) 
    {
        $code = $_request->query('code');

        if ($this->referralCodeService->isCodeValid($code)) 
        {
            $data = UserData::getColumnNames();
            return $this->apiResponseService->success($data, 'Code accepted', 200);
        }

        return $this->apiResponseService->error('Code rejected', 400);
    }

}
