<?php

namespace App\Http\Controllers\Api;

use App\Services\ApiResponseService;

use Illuminate\Http\Request;

class UserController 
{
    private ApiResponseService $apiResponseService;

    public function __construct(ApiResponseService $_apiResponseService)
    {
        $this->apiResponseService = $_apiResponseService;
    }
}