<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Enums\TicketLogTypeEnum;
use App\Models\SystemSetting;
use App\Models\Ticket;
use App\Services\ApiResponseService;
use App\Services\Reports\PeriodicReport;
use App\Services\Reports\TicketReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use Throwable;

class TicketReportController extends Controller
{
    public function __construct(
        protected ApiResponseService $apiResponseService,
        protected PeriodicReport $periodicReport,
        protected TicketReport $ticketReport,
    ){}

    public function statistics()
    {
        try
        {
            $tickets = Ticket::all();

            $grouped = $tickets->groupBy(function ($ticket) {
                return Carbon::parse($ticket->raised_on)->format('Y-m');
            });

            $response_data = $grouped->map(function ($items, $yearMonth) {
                $date = Carbon::createFromFormat('Y-m', $yearMonth);

                $monthLower = strtolower($date->format('F')); 

                return [
                    'month' => $date->format('F'),
                    'year' => $date->format('Y'),
                    'periodic_report' => env('APP_URL') . "/api/statistics/{$monthLower}/report",
                    'ticket_summarization' => env('APP_URL') . "/api/statistics/{$monthLower}/tickets",
                ];
            })->values();

            return $this->apiResponseService->ok($response_data, 'List of available months with report and summarization.');
        }
        catch(Throwable $e)
        {
            Log::error('An error occurred while retriving statistics', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        
            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }

    public function periodic_report(string $month)
    {
        $validator = Validator::make(['month' => $month], [
            'month' => 'required|string|in:january,february,march,april,may,june,july,august,september,october,november,december',
        ]);

        if ($validator->fails()) {
            return $this->apiResponseService->badRequest('Validation failed.', $validator->errors());
        }

        try {

            $month_in_number = Carbon::parse('1' . ucfirst($month))->month;
            
            $periodic_report = $this->periodicReport->generate($month_in_number);
            return $this->apiResponseService->raw($periodic_report, 'application/pdf', 'periodic_report.pdf');

        } catch (Throwable $e) {
            
            Log::error('An error occurred while generating periodic report', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        
            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }

    public function ticket_report(string $month)
    {
        $validator = Validator::make(['month' => $month], [
            'month' => 'required|string|in:january,february,march,april,may,june,july,august,september,october,november,december',
        ]);

        if ($validator->fails()) {
            return $this->apiResponseService->badRequest('Validation failed.', $validator->errors());
        }

        try
        {
            
            $month_in_number = Carbon::parse('1' . ucfirst($month))->month;
            
            $ticket_report = $this->ticketReport->generate(
                $month_in_number,
            );

            return $this->apiResponseService->raw($ticket_report, 'application/pdf', 'periodic_report.pdf');

        }
        catch (Throwable $e)
        {
            Log::error('An error occurred while generating ticket report', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        
            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }
}
