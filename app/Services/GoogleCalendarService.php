<?php

namespace App\Services;

use App\Models\SystemSetting;

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\Calendar as GoogleCalendar;

use Illuminate\Support\Facades\Log;

use Exception;

class GoogleCalendarService
{
    public function getClient()
    {
        $googleClientId = SystemSetting::get('google_client_id');
        $googleClientSecret = SystemSetting::get('google_client_secret');
        $googleRedirectUri = SystemSetting::get('google_redirect_uri');

        if (!$googleClientId || !$googleClientSecret || !$googleRedirectUri) {
            return null;
        }

        $client = new Client();
        $client->setClientId($googleClientId);
        $client->setClientSecret($googleClientSecret);
        $client->setRedirectUri($googleRedirectUri);

        $client->addScope(Calendar::CALENDAR);
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        return $client;
    }

    private function refreshAccessToken(Client $client)
    {
        $access_token = SystemSetting::get('google_access_token');
        $client->setAccessToken($access_token);

        if ($client->isAccessTokenExpired()) {
            $refresh_token = SystemSetting::get('google_refresh_token');
            $token_data = $client->fetchAccessTokenWithRefreshToken($refresh_token);

            if (isset($token_data['error'])) {
                throw new Exception('Failed to refresh access token.');
            }

            SystemSetting::put('google_access_token', $token_data['access_token']); 

            if (!empty($token_data['refresh_token'])) {
                SystemSetting::put('google_refresh_token', $token_data['refresh_token']);
            }
        }
    }

    public function createEvent(array $eventData, string $calendarId = 'primary')
    {
        try {
            $client = $this->getClient();
            $this->refreshAccessToken($client);

            $service = new Calendar($client);

            $event = new Event([
                'summary' => $eventData['summary'],
                'location' => $eventData['location'] ?? null,
                'description' => $eventData['description'] ?? null,
                'start' => [
                    'dateTime' => $eventData['start'],
                    'timeZone' => 'Asia/Jakarta'
                ],
                'end' => [
                    'dateTime' => $eventData['end'],
                    'timeZone' => 'Asia/Jakarta'
                ]
            ]);

            return $service->events->insert($calendarId, $event);
        } 
        catch (Exception $e) 
        {
            Log::error('Google Calendar Event Creation Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw new Exception('Unable to create event.');
        }
    }

    public function createCalendar(string $calendarName)
    {
        try {
            $client = $this->getClient();
            $this->refreshAccessToken($client);

            $service = new Calendar($client);

            $calendar = new GoogleCalendar([
                'summary' => $calendarName,
                'timeZone' => 'Asia/Jakarta'
            ]);
            
            return $service->calendars->insert($calendar);
        } 
        catch (Exception $e) 
        {
            Log::error('Google Calendar Creation Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new Exception('Unable to create calendar.');
        }
    }

    public function getEvents(string $calendarId = 'primary', array $params = [])
    {
        try {
            $client = $this->getClient();
            $this->refreshAccessToken($client);

            $service = new Calendar($client);

            $defaultParams = [
                'singleEvents' => true,
                'orderBy' => 'startTime',
                'timeMin' => date('c'), 
            ];

            $options = array_merge($defaultParams, $params);

            $events = $service->events->listEvents($calendarId, $options);

            return $events->getItems();
        } 
        catch (Exception $e) 
        {
            Log::error('Google Calendar Event Fetch Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new Exception('Unable to fetch events.');
        }
    }

}
