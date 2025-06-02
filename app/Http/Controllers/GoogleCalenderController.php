<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;

use App\Services\ApiResponseService;
use App\Services\GoogleCalendarService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

class GoogleCalenderController extends Controller
{
    private GoogleCalendarService $googleCalendarService;
    private ApiResponseService $apiResponseService;

    public function __construct(
        GoogleCalendarService $_googleCalendarService,
        ApiResponseService $_apiResponseService
    ) {
        $this->googleCalendarService = $_googleCalendarService;
        $this->apiResponseService = $_apiResponseService;
    }

    public function initializeClient(Request $_request)
    {
        
        $google_client_id = $_request->input('google_client_id');
        $google_client_secret = $_request->input('google_client_secret');
        $google_redirect_uri = $_request->input('google_redirect_uri');

        $validator = Validator::make($_request->all(), [
            'google_client_id' => 'required|string',
            'google_client_secret' => 'required|string',
            'google_redirect_uri' => 'required|string',
        ]);

        if($validator->fails())
        {
            return $this->apiResponseService->badRequest('Validation failed for Google client configuration.' ,$validator->errors());
        }

        try
        {
            SystemSetting::put('google_client_id', $google_client_id);
            SystemSetting::put('google_client_secret', $google_client_secret);
            SystemSetting::put('google_redirect_uri', $google_redirect_uri);

            $response_data = [
                'google_client_id' => $google_client_id,
                'google_client_secret' => $google_client_secret,
                'google_redirect_uri' => $google_redirect_uri,
            ];

            return $this->apiResponseService->ok($response_data,'Google client configuration saved successfully.');
        }
        catch (Throwable $e)
        {
            Log::error('Failed to save Google client configuration.', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('An unexpected error occurred while saving the Google client configuration.');
        }
    }

    public function auth()
    {
        try 
        {
            $client = $this->googleCalendarService->getClient();

            if(!$client) {
                return redirect('/');
            }

            $authUrl = $client->createAuthUrl();

            return redirect($authUrl);
        } 
        catch (Throwable $e) 
        {
            Log::error('Google auth error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('Failed to initiate Google authentication.');
        }
    }

    public function callback()
    {
        try 
        {
            $client = $this->googleCalendarService->getClient();
            $token_data = $client->fetchAccessTokenWithAuthCode(request('code'));

            if (isset($accessToken['error'])) {
                return $this->apiResponseService->unauthorized('Failed to retrieve access token.');
            }
            
            SystemSetting::put('google_access_token', $token_data['access_token']);
            SystemSetting::put('google_refresh_token', $token_data['refresh_token']);

            return redirect()
                ->route('settings.calender')
                ->with('success', 'Settings updated successfully!');
        } 
        catch (Throwable $e) 
        {
            Log::error('Google callback error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('An error occurred during token exchange.');
        }
    }
}
