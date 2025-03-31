<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Session;

use App\Services\WebAuthTokenService;

class WebAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = Session::get('auth_token');

        if (!$token || !WebAuthTokenService::checkValidToken($token)) {
            return redirect('/auth'); 
        }

        return $next($request);
    }
}
