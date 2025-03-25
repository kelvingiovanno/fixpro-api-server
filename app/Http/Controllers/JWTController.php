<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

class JWTController extends Controller
{
    public function generateToken()
    {
        $customClaims = [
            'sub' => 'profix_api_service', 
            'iat' => Carbon::now()->timestamp, 
            'exp' => Carbon::now()->addHours(2)->timestamp, 
        ];

        $payload = JWTAuth::factory()->customClaims($customClaims)->make();

        $token = JWTAuth::encode($payload)->get();

        return response()->json([
            'token' => $token,
            'data' => $customClaims
        ]);
    }
}
