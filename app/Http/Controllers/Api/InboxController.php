<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inbox;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class InboxController extends Controller
{
    public function __construct(
        protected ApiResponseService $apiResponseService,
    ){}

    public function index(Request $request)
    {
        try
        {
            $client_id = $request->client['id'];

            $inboxes = Inbox::where('member_id', $client_id)->get();

            $response_data = $inboxes->map(function ($inbox) {
                return [
                    'id' => $inbox->id,
                    'title' => $inbox->title,
                    'boyd' => $inbox->body,
                    'sent_on' => $inbox->sent_on->format('Y-m-d\TH:i:sP'),
                    'actionable' => [
                        'genus' => 'SEGUE',
                        'species' => 'TICKET',
                        'segue_destination' => $inbox->ticket_id,
                    ],
                ];
            });

            return $this->apiResponseService->ok($response_data, 'Inbox retrieved successfully.');
        }
        catch(Throwable $e)
        {
            Log::error('An error occurred while retriving client inbox ', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }
}
