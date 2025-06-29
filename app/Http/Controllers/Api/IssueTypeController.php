<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Calender;
use App\Models\Enums\TicketIssueType;

use App\Services\ApiResponseService;
use App\Services\AreaService;
use App\Services\GoogleCalendarService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use Illuminate\Database\Eloquent\ModelNotFoundException;

use Throwable;

class IssueTypeController extends Controller
{
    public function __construct(
        protected ApiResponseService $apiResponseService,
        protected GoogleCalendarService $googleCalendarService,
        protected AreaService $areaService,
        
    ) { }

    public function index() 
    {
        try
        {
            $issue_types = TicketIssueType::all();

            $response_data = $issue_types->map(function ($issue) {
                return [
                    'id' => $issue->id,
                    'name' => $issue->name,
                    'service_level_agreement_duration_hour' => (string) $issue->sla_hours,
                ];
            });

            return $this->apiResponseService->ok($response_data ,'A list of issue types.');
        }
        catch (Throwable $e)
        {
            Log::error('An error occurred while retrieving issue types: ', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.name' => 'required|string',
            'data.service_level_agreement_duration_hour' => 'required|integer',
        ]);

        if($validator->fails())
        {
            return $this->apiResponseService->badRequest('Validation failed.', $validator->errors());
        }

        try
        {
            $name = $request['data']['name'];
            $sla = $request['data']['service_level_agreement_duration_hour'];

            $existing = TicketIssueType::withTrashed()
                ->where('name', $name)
                ->first();

            if ($existing) 
            {
                if ($existing->trashed()) 
                {
                    $existing->restore();
                    $existing->update(['sla_hours' => $sla]); 

                    $issue_type = $existing;


                    if($this->areaService->is_calendar_setup())
                    {
                        $new_calender = $this->googleCalendarService->create_calender($name);

                        Calender::create([
                            'id' => $new_calender->getId(),
                            'name' => $new_calender->getSummary(),
                        ]);
                    }
                } 
                else 
                {
                    return $this->apiResponseService->badRequest('The name already exists.');
                }
            } 
            else 
            {
                $issue_type = TicketIssueType::create([
                    'name' => $name,
                    'sla_hours' => $sla,
                ]);

                if ($this->areaService->is_calendar_setup()) 
                {
                    $new_calender = $this->googleCalendarService->create_calender($name);

                    Calender::create([
                        'id' => $new_calender->getId(),
                        'name' => $new_calender->getSummary(),
                    ]);
                }
            }

            $response_data = [
                'id' => $issue_type->id,
                'name' => $issue_type->name,
                'service_level_agreement_duration_hour' => (string) $issue_type->sla_hours,
            ];

            return $this->apiResponseService->created($response_data, 'Issue type created successfully.');
            
        }
        catch (Throwable $e)
        {
            Log::error('An error occurred while creating issue type: ', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }

    public function destroy(string $issue_id)
    {
        $validator = Validator::make(['issue_id' => $issue_id], 
        [
            'issue_id' => 'required|uuid',
        ]);

        if($validator->fails())
        {
            return $this->apiResponseService->badRequest('Validation failed.', $validator->errors());
        }

        try
        {
            $issue_type = TicketIssueType::findOrFail($issue_id);

            if(!$issue_type)
            {
                return $this->apiResponseService->badRequest('Issue type not found.');
            }

            $issue_type->delete();

            if($this->areaService->is_calendar_setup())
            {
                $calender = Calender::where('name', $issue_type->name)->first();
                $calender_id = $calender->id;

                $this->googleCalendarService->delete_calender($calender_id);

                $calender->forceDelete();
            }

            $response_data = [
                'id' => $issue_type->id,
                'name' => $issue_type->name,
                'service_level_agreement_duration_hour' => (string) $issue_type->sla_hours,
            ];

            return $this->apiResponseService->ok($response_data, 'Issue type deleted successfully.');
        }
        catch (ModelNotFoundException $e)
        {
            return $this->apiResponseService->notFound('Issue type not found.');
        }
        catch (Throwable $e)
        {
            Log::error('An error occurred while deleting issue type: ', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }
}
