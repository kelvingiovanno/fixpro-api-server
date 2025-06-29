<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Services\ApiResponseService;
use App\Services\CalenderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class CalenderController extends Controller
{
    public function __construct(
        protected ApiResponseService $apiResponseService ,
        protected CalenderService $calenderService,
    ){}

    public function show_events(Request $request)
    {
        try
        {
            $client_id = $request->client['id'];
            $client_role_id = $request->client['role_id'];

            $response_data = $this->calenderService->get_all_events(
                $client_id,
                $client_role_id
            );

            return $this->apiResponseService->ok($response_data, 'Successfully retrieved all events from Google.');
        }   
        catch(Throwable $e)
        {
            Log::error('An error occurred while retriving calender events.', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(), 
            ]);
    
            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }
}
