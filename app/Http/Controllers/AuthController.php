<?php

namespace App\Http\Controllers;

use App\Services\AuthTokenService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected AuthTokenService $authTokenService;

    public function __construct(AuthTokenService $authTokenService)
    {
        $this->authTokenService = $authTokenService;
    }

    public function index()
    {
        return view('auth');
    }

    public function login(Request $request)
    {
        $request->validate([
            'auth_token' => 'required|string',
        ]);

        if ($this->authTokenService::checkValidToken($request->auth_token)) {
            session(['auth_token' => $request->auth_token]);

            return redirect()->route('auth.form')->with('success', 'Login successful!');
        }

        return redirect()->route('auth.form')->with('error', 'Invalid token. Please try again.');
    }

    public function logout()
    {
        session()->forget('auth_token'); 

        return redirect()->route('auth.form');
    }
}
