<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Enums\TicketIssueType;

use App\Services\ApiResponseService;
use App\Services\AreaService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use Throwable;

class SlaController extends Controller
{
    public function __construct(
       protected ApiResponseService $apiResponseService,
       protected AreaService $areaService,
    ) { }

    public function index()
    {
        try
        {
            $per_issue_types = TicketIssueType::all();

            $reponse_data = [
                'sla_to_respond' => $this->areaService->get_sla_response(),
                'sla_to_auto_close' => $this->areaService->get_sla_close(),
                'per_issue_types' => $per_issue_types->map(function ($issue) {
                    return [
                        'id' => $issue->id,
                        'name' => $issue->name,
                        'service_level_agreement_duration_hour' => $issue->sla_hours,
                    ];
                }),
            ];

            return $this->apiResponseService->ok($reponse_data, 'SLA retrieved successfully.');
        }
        catch (Throwable $e)
        {
            Log::error('An error occurred while retrieving SLA', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.sla_to_respond' => 'required|string',
            'data.sla_to_auto_close' => 'required|string',
            'data.per_issue_types' => 'required|array',
            'data.per_issue_types.*.id' => 'required|uuid|exists:ticket_issue_types,id',
            'data.per_issue_types.*.duration' => 'required|integer',
        ]);

        if($validator->fails())
        {
            return $this->apiResponseService->badRequest('Validation failed.',$validator->errors());
        }

        try
        {
            $sla_to_reponse = $this->areaService->set_sla_response($request->data['sla_to_respond']);
            $sla_to_auto_close = $this->areaService->set_sla_close($request->data['sla_to_auto_close']);

            foreach($request->data['per_issue_types'] as $issue)
            {               
                $find_issue = TicketIssueType::find($issue['id']);
                $find_issue->update([
                    'sla_hours' => $issue['duration'],
                ]);
            }

            $issues = TicketIssueType::all();

            $reponse_data = [
                'sla_to_respond' => $sla_to_reponse,
                'sla_to_auto_close' => $sla_to_auto_close,
                'per_issue_types' => $issues->map(function ($issue) {
                    return [
                        'id' => $issue->id,
                        'name' => $issue->name,
                        'duration' => $issue->sla_hours,
                    ];
                }),
            ];

            return $this->apiResponseService->ok($reponse_data, 'SLA updated successfully.');   
        }
        catch (Throwable $e)
        {
            Log::error('An error occurred while updating SLA', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }
}
