<?php

namespace App\Http\Controllers;

use App\Services\WebAuthTokenService;
use Illuminate\Http\Request;

class WebAuthPageController extends Controller
{
    protected WebAuthTokenService $webAuthTokenService;

    public function __construct(WebAuthTokenService $_webAuthTokenService)
    {
        $this->webAuthTokenService = $_webAuthTokenService;
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

        if ($this->webAuthTokenService::checkValidToken($request->auth_token)) {
            session(['auth_token' => $request->auth_token]);

            return redirect('/');
        }

        return redirect()->route('auth.form')->with('error', 'Invalid token. Please try again.');
    }

    public function logout()
    {
        session()->forget('auth_token'); 

        return redirect()->route('auth.form');
    }
}
