<?php

namespace App\Services;

use App\Enums\IssueTypeEnum;
use App\Enums\MemberRoleEnum;

use App\Models\Calender;
use App\Models\Enums\TicketIssueType;
use App\Models\Event;
use App\Models\Member;
use App\Models\Ticket;
use App\Services\GoogleCalendarService;

use Carbon\Carbon;
use Illuminate\Support\Arr;

class CalenderService
{
    public function __construct(
        protected GoogleCalendarService $googleCalendarService,
        protected AreaService $areaService,
    ){}

    public function get_all_events(
        string $requester_id,
        string $requester_role_id,
    ) {
        if ($requester_role_id == MemberRoleEnum::CREW->id()) 
        {
            $specialities = Member::findOrFail($requester_id)->specialities;
            

            $response_data = [];

            foreach ($specialities as $speciality) {
                
                $calendar = Calender::where('name', $speciality->name)->first();

                if (!$calendar) {
                    continue;
                }

                $events = $calendar->events;

                foreach ($events as $event) {
                    
                    $startUtc = Carbon::parse($event->start)->format('Ymd\THis\Z');
                    $endUtc = Carbon::parse($event->end)->format('Ymd\THis\Z');

                    $title = urlencode($event->summary);
                    $description = urlencode($event->description ?? '');
                    $location = urlencode($event->location ?? '');

                    $googleCalendarLink = "https://calendar.google.com/calendar/render?"
                        . "action=TEMPLATE"
                        . "&text={$title}"
                        . "&dates={$startUtc}/{$endUtc}"
                        . "&details={$description}"
                        . "&location={$location}";

                    $response_data[] = [
                        'id' => $event->id,
                        'event_title' => $event->summary,
                        'event_description' => $event->description,
                        'happening_on' => $event->start->format('Y-m-d\TH:i:sP'),
                        'duration_in_seconds' =>  $event->start->diffInSeconds($event->end),
                        'reminder_enebled' => true,
                        'saved_on' => $googleCalendarLink,
                        'effected_ticket' => $event->tikcet->id,
                    ];
                }
            }

            return $response_data;
        }

        $calendars = Calender::all();
        $response_data = [];

        foreach ($calendars as $calendar) {
            
            $events = $calendar->events;

            foreach ($events as $event) {

                $startUtc = Carbon::parse($event->start)->format('Ymd\THis\Z');
                $endUtc = Carbon::parse($event->end)->format('Ymd\THis\Z');

                $title = urlencode($event->summary);
                $description = urlencode($event->description ?? '');
                $location = urlencode($event->location ?? '');

                $googleCalendarLink = "https://calendar.google.com/calendar/render?"
                    . "action=TEMPLATE"
                    . "&text={$title}"
                    . "&dates={$startUtc}/{$endUtc}"
                    . "&details={$description}"
                    . "&location={$location}";

                $response_data[] = [
                    'id' => $event->id,
                    'event_title' => $event->summary,
                    'event_description' => $event->description,
                    'happening_on' => $event->start->format('Y-m-d\TH:i:sP'),
                    'duration_in_seconds' =>  $event->start->diffInSeconds($event->end),
                    'reminder_enebled' => true,
                    'effected_ticket' => $event->tikcet->id,
                ];
            }
        }

        return $response_data;
    }

    public function create_event(
        Ticket $ticket, 
        string $summary,
        string $description,
        string $issue
    ) {
        $calendar_id = Calender::where('name', $issue)->first()->id;

        $target_issue = TicketIssueType::from($issue)->id;
        
        $start_time = now()->addHours($target_issue->sla_hours + ($ticket->response->sla_modifier * $this->areaService->get_sla_response()))->toRfc3339String();

        $event_data = [
            'summary' => $summary,
            'description' => $description,
            'start' => $start_time,
            'end' => $start_time,
        ];

        $ticket->calender_event()->create(array_merge(
            $event_data,
            ['calender_id' => $calendar_id],
        ));

        $this->googleCalendarService->create_event(
            $event_data,
            $calendar_id,
        );
    }

    public function create_calender(string $calendarName)
    {
        $new_calender = $this->googleCalendarService->create_calender($calendarName);

        Calender::create([
            'id' => $new_calender->getId(), 
            'name' => $calendarName,
        ]);

    }
}