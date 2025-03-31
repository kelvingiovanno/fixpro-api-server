<?php

namespace App\Http\Controllers\Api;

use App\Models\User;

use App\Services\ApiResponseService;

use Illuminate\Http\Request;

class UserController 
{

    private ApiResponseService $apiResponseService;

    public function __construct(ApiResponseService $_apiResponseService)
    {
        $this->apiResponseService = $_apiResponseService;
    }

    public function getAllUsersWithUserData()
    {
        $users = User::with('userData')->get();

        return response()->json($users);
    }

    public function isUserStatusAccepted($id)
    {

        $user = User::find($id);
        
        if (!$user) {
            return $this->apiResponseService->error(false, 404, "User not found");
        }

        if ($user->isAccepted($id)) {
            return $this->apiResponseService->success(true, "User approved", 200);
        }

        return $this->apiResponseService->error(false, 403, "User not approved");
    }


    public function ApproveUserStatus(Request $_request)
    {
        
    }
}