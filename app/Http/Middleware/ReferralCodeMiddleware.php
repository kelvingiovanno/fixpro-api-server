<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\ReferralCodeService;
use App\Services\ApiResponseService;

class ReferralCodeMiddleware
{
    protected $referralCodeService;
    protected $apiResponseService;

    public function __construct(ReferralCodeService $referralCodeService, ApiResponseService $apiResponseService)
    {
        $this->referralCodeService = $referralCodeService;
        $this->apiResponseService = $apiResponseService;
    }

    public function handle(Request $request, Closure $next)
    {
        $code = $request->query('code');

        if (!$code || !$this->referralCodeService->isCodeValid($code)) {
            return $this->apiResponseService->error('Invalid or missing referral code', 400);
        }

        return $next($request);
    }
}
