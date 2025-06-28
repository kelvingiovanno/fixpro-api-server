<?php

namespace App\Services;

use App\Exceptions\GoogleCalenderException;
use App\Models\SystemSetting;

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\Calendar as GoogleCalendar;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use Exception;

class GoogleCalendarService
{
    public function get_client_id()
    {
        $client_id = Cache::get('google_client_id');

        if(!$client_id)
        {
            $client_id = SystemSetting::get('google_client_id');
            Cache::forever('google_client_id', $client_id);
        }

        return $client_id;
    }

    public function get_client_secret()
    {
        $client_secret = Cache::get('google_client_secret');

        if(!$client_secret)
        {
            $client_secret = SystemSetting::get('google_client_secret');
            Cache::forever('google_client_secret', $client_secret);
        }

        return $client_secret;
    }

    public function get_redirect_uri()
    {
        $redirect_uri = Cache::get('google_redirect_uri');

        if(!$redirect_uri)
        {
            $redirect_uri = SystemSetting::get('google_redirect_uri');
            Cache::forever('google_redirect_uri', $redirect_uri);
        }

        return $redirect_uri;
    }

    public function get_access_token()
    {
        $access_token = Cache::get('google_access_token');

        if(!$access_token)
        {
            $access_token = SystemSetting::get('google_access_token');
            Cache::forever('google_access_token', $access_token);
        }

        return $access_token;
    }

    public function get_refresh_token()
    {
        $refresh_token = Cache::get('google_refresh_token');

        if(!$refresh_token)
        {
            $refresh_token = SystemSetting::get('google_refresh_token');
            Cache::forever('google_refresh_token', $refresh_token);
        }

        return $refresh_token;
    }

    public function set_client_id(string $client_id)
    {
        SystemSetting::put('google_client_id', $client_id);
        Cache::forever('google_client_id', $client_id);

        return $client_id;
    }

    public function set_client_secret(string $client_secret)
    {
        SystemSetting::put('google_client_secret', $client_secret);
        Cache::forever('google_client_secret', $client_secret);

        return $client_secret;
    }

    public function set_redirect_uri(string $redirect_uri)
    {
        SystemSetting::put('google_redirect_uri', $redirect_uri);
        Cache::forever('google_redirect_uri', $redirect_uri);

        return $redirect_uri;
    }

    public function set_access_token(string $access_token)
    {
        SystemSetting::put('google_access_token', $access_token);
        Cache::forever('google_access_token', $access_token);

        return $access_token;
    }

    public function set_refresh_token(string $refresh_token)
    {
        SystemSetting::put('google_refresh_token', $refresh_token);
        Cache::forever('google_refresh_token', $refresh_token);

        return $refresh_token;
    }

    public function build_client()
    {
        $client_id = $this->get_client_id();
        $client_secret = $this->get_client_secret();
        $redirect_uri = $this->get_redirect_uri();

        if (!$client_id || !$client_secret || !$redirect_uri) {
            throw new GoogleCalenderException('Google Calendar credentials are missing.');
        }
        
        $client = new Client();
        
        $client->setClientId($client_id);
        $client->setClientSecret($client_secret);
        $client->setRedirectUri($redirect_uri);

        $client->addScope(Calendar::CALENDAR);
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        return $client;
    }

    public function client()
    {

        $client = $this->build_client();

        $access_token = $this->get_access_token();

        if(!$access_token)
        {
            throw new GoogleCalenderException('Google Calendar access token is missing.');
        }

        $client->setAccessToken($access_token);

        if ($client->isAccessTokenExpired()) {

            $refresh_token = $this->get_refresh_token();

            $fetch_response = $client->fetchAccessTokenWithRefreshToken($refresh_token);

            if (isset($fetch_response['error'])) {
                throw new GoogleCalenderException('Failed to refresh access token.');
            }

            $new_access_token = $this->set_access_token($fetch_response['access_token']);
            $this->set_refresh_token($fetch_response['refresh_token']);

            $client->setAccessToken($new_access_token);
        }

        return $client;
    }

    public function create_event(array $eventData, string $calendarId)
    {
        try {
            $client = $this->client();

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

    public function create_calender(string $calendarName)
    {
        try {
            $client = $this->client();

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

    public function get_events(string $calendarId = 'primary', array $params = [])
    {
        try {
            $client = $this->client();

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
