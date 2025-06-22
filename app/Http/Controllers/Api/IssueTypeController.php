<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Calender;
use App\Models\Enums\TicketIssueType;

use App\Services\ApiResponseService;
use App\Services\GoogleCalendarService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use Throwable;

class IssueTypeController extends Controller
{
    private ApiResponseService $apiResponseService;
    private GoogleCalendarService $googleCalendarService;

    public function __construct(
        ApiResponseService $_apiResponseService,
        GoogleCalendarService $_googleCalendarService,
        
    ) {
        $this->apiResponseService = $_apiResponseService;
        $this->googleCalendarService = $_googleCalendarService;
    }

    public function index() 
    {
        try
        {
            $issue_types = TicketIssueType::all();

            $response_data = $issue_types->map(function ($issue) {
                return [
                    'id' => $issue->id,
                    'name' => $issue->name,
                    'service_level_agreement_duration_hour' => $issue->sla_duration_hour ?? 'Not assigned yet',
                ];
            });

            return $this->apiResponseService->ok($response_data ,'A list of issue types.');
        }
        catch (Throwable $e)
        {
            Log::error('Error retrieving issue types: ', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            return $this->apiResponseService->internalServerError('An error occurred while retrieving issue types.');
        }
    }

    public function store(Request $_request)
    {
        $data = $_request->input('data');

        $validator = Validator::make($data, [
            'name' => 'required|string',
            'service_level_agreement_duration_hour' => 'required|integer',
        ]);

        if($validator->fails())
        {
            return $this->apiResponseService->badRequest('Validation failed.', $validator->errors());
        }

        try
        {
            $issue_type = TicketIssueType::create([
                'name' => $data['name'],
                'sla_duration_hour' => $data['service_level_agreement_duration_hour'],
            ]);

            $new_issue_type = TicketIssueType::find($issue_type->id);

            $new_calender = $this->googleCalendarService->createCalendar($data['name']);
            Calender::create([
                'id' => $new_calender->getId(), 
                'name' => $new_calender->getSummary(),
            ]);

            $response_data = [
                'id' => $new_issue_type->id,
                'name' => $new_issue_type->name,
                'service_level_agreement_duration_hour' => $new_issue_type->sla_duration_hour,
            ];

            return $this->apiResponseService->created($response_data, 'Issue type created successfully.');
        }
        catch (Throwable $e)
        {
            Log::error('Error creating issue type: ', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            return $this->apiResponseService->internalServerError('An error occurred while creating issue type.');
        }
    }

    public function destroy(string $_issueTypeId)
    {
        if(!Str::isUuid($_issueTypeId))
        {
            return $this->apiResponseService->badRequest('Issue type not found.');
        }

        try
        {
            $issue_type = TicketIssueType::find($_issueTypeId);

            if(!$issue_type)
            {
                return $this->apiResponseService->badRequest('Issue type not found.');
            }

            $issue_type->delete();

            $response_data = [
                'id' => $issue_type->id,
                'name' => $issue_type->name,
                'service_level_agreement_duration_hour' => $issue_type->sla_duration_hour ?? 'Not assigned yet',
            ];

            return $this->apiResponseService->ok($response_data, 'Issue type deleted successfully.');
        }
        catch (Throwable $e)
        {
            Log::error('Error deleting issue type: ', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            return $this->apiResponseService->internalServerError('An error occurred while deleting issue type.');
        }
    }
}
