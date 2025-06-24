<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
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
            $sla_to_reponse = SystemSetting::get('sla_response') ?? 86400;
            $sla_to_auto_close = SystemSetting::get('sla_auto_close') ?? 86400;
            $per_issue_types = TicketIssueType::all();

            $reponse_data = [
                'sla_to_respond' => $sla_to_reponse,
                'sla_to_auto_close' => $sla_to_auto_close,
                'per_issue_types' => $per_issue_types->map(function ($issue) {
                    return [
                        'id' => $issue->id,
                        'name' => $issue->name,
                        'duration' => $issue->sla_hours,
                    ];
                }),
            ];

            return $this->apiResponseService->ok($reponse_data, '');
        }
        catch (Throwable $e)
        {
            Log::error('Failed to retrieve sla data', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('Failed to retrieve area data');
        }
    }

    public function update(Request $_request)
    {
        $payload = $_request->input('data');    

        $validator = Validator::make($payload, [
            'sla_to_respond' => 'required|integer',
            'sla_to_auto_close' => 'required|integer',
            'per_issue_types' => 'required|array',
            'per_issue_types.*.id' => 'required|uuid|exists:ticket_issue_types,id',
            'per_issue_types.*.duration' => 'required|integer',
        ]);

        if($validator->fails())
        {
            return $this->apiResponseService->badRequest('Validation failed.',$validator->errors());
        }

        try
        {
            $sla_to_reponse = SystemSetting::put('sla_response', $payload['sla_to_respond']);
            $sla_to_auto_close = SystemSetting::put('sla_response', $payload['sla_to_auto_close']);

            foreach($payload['per_issue_types'] as $issue)
            {               
                $find_issue = TicketIssueType::find($issue['id']);
                $find_issue->update([
                    'sla_hours' => $issue['duration'],
                ]);
            }

            $per_issue_types = TicketIssueType::all();

            $reponse_data = [
                'sla_to_respond' => $sla_to_reponse,
                'sla_to_auto_close' => $sla_to_auto_close,
                'per_issue_types' => $per_issue_types->map(function ($issue) {
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
            Log::error('Failed to update sla data', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('Failed to retrieve area data');
        }
    }
}
