<?php  

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Enums\ApplicantStatusEnum;
use App\Models\Enums\TicketIssueType;

use App\Models\Applicant;
use App\Models\SystemSetting;

use App\Services\ApiResponseService;
use App\Services\AreaService;
use Illuminate\Support\Facades\Log;

use Throwable;

class AreaController extends Controller
{

    public function __construct (
        protected ApiResponseService $apiResponseService, 
        protected AreaService $areaService,       
    ) {}
    
    public function index()
    {
        try     
        {
            $reponse_data = [
                'name' => $this->areaService->get_name(),
                'join_policy' => $this->areaService->get_join_policy(),
                'member_count' => Applicant::where('status_id', ApplicantStatusEnum::ACCEPTED->id())->count(),
                'pending_member_count' => Applicant::where('status_id', ApplicantStatusEnum::PENDING->id())->count(), 
                'issue_type_count' => TicketIssueType::all()->count(),
            ];

            return $this->apiResponseService->ok($reponse_data, 'Area data retrieved successfully.');
        } 
        catch (Throwable $e) 
        {
            Log::error('Error occurred while retrieving area data', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }
}