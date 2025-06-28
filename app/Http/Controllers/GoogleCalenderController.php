<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Exceptions\GoogleCalenderException;

use App\Models\Calender;

use App\Models\Enums\TicketIssueType;

use App\Services\ApiResponseService;
use App\Services\CalenderService;
use App\Services\GoogleCalendarService;

use Illuminate\Support\Facades\Log;

use Throwable;

class GoogleCalenderController extends Controller
{
    public function __construct(
        protected CalenderService $calenderService,
        protected ApiResponseService $apiResponseService,
        protected GoogleCalendarService $googleCalendarService,
    ) { }

    public function auth()
    {
        try 
        {
            $client = $this->googleCalendarService->build_client();

            $authUrl = $client->createAuthUrl();
            
            return redirect($authUrl);
        } 
        catch (GoogleCalenderException $e)
        {
            return $this->apiResponseService->forbidden($e->getMessage());
        }
        catch (Throwable $e) 
        {
            Log::error('An error occurred during Google authentication', [
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
            $client = $this->googleCalendarService->build_client();
            $token_data = $client->fetchAccessTokenWithAuthCode(request('code'));

            if (isset($token_data['error'])) {
                return $this->apiResponseService->unauthorized('Failed to retrieve access token.');
            }
            
            $this->googleCalendarService->set_access_token($token_data['access_token']);
            $this->googleCalendarService->set_refresh_token($token_data['refresh_token']);

            $issues = TicketIssueType::all();

            foreach ($issues as $issue)
            {
                $this->calenderService->create_calender($issue->name);
            }

            return redirect()
                ->route('settings.calender')
                ->with('success', 'Settings updated successfully!');
        } 
        catch (GoogleCalenderException $e)
        {
            return $this->apiResponseService->forbidden($e->getMessage());
        }
        catch (Throwable $e) 
        {
            Log::error('An error occurred during the Google callback process', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('An error occurred during token exchange.');
        }
    }
}
