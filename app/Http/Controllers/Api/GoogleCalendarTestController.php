<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;


use Exception;

class GoogleCalendarTestController extends Controller
{
    public function __construct(
        protected GoogleCalendarService $googleCalendarService
    ) {}

    public function createCalendar(Request $request)
    {
        $name = $request->input('name', 'Test Calendar');
        try {
            $calendar = $this->googleCalendarService->create_calender($name);
            return response()->json([
                'message' => 'Calendar created',
                'data' => [
                    'id' => $calendar->getId(),
                    'name' => $calendar->getSummary(),
                ]
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function createEvent(Request $request)
    {
        try {
            $event = $this->googleCalendarService->create_event([
                'summary' => 'Test Event',
                'description' => 'This is a test event',
                'location' => 'Jakarta',
                'start' => now()->addHour()->toRfc3339String(),
                'end' => now()->addHours(2)->toRfc3339String(),
            ], $request->input('calendar_id', 'primary'));

            return response()->json([
                'message' => 'Event created',
                'data' => $event
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function getEvents(Request $request)
    {
        try {
            $events = $this->googleCalendarService->get_events($request->input('calendar_id', 'primary'));
            return response()->json([
                'message' => 'Events fetched',
                'data' => $events
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
