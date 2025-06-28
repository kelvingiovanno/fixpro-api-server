<?php  

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Enums\ApplicantStatusEnum;
use App\Enums\JoinPolicyEnum;

use App\Models\Enums\TicketIssueType;

use App\Models\Applicant;

use App\Services\ApiResponseService;
use App\Services\AreaService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use Throwable;
use ValueError;

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

            dd($reponse_data);

            return $this->apiResponseService->ok($reponse_data, 'Area data retrieved successfully.');
        } 
        catch (Throwable $e) 
        {
            Log::error('An error occurred while retrieving area data', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }
    
    public function get_join_policy()
    {
        try
        {
            $join_policy = $this->areaService->get_join_policy();

            $response_data = [
                'join_policy' => $join_policy,
            ];

            return $this->apiResponseService->ok($response_data, 'join_policy retrieved successfully.');
        }
        catch(Throwable $e)
        {
            Log::error('An error occurred while retrieving join policy', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }

    public function update_join_policy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.join_policy' => 'required|string'
        ],
        [
            'data.required' => 'The data field is required.',
            'data.array' => 'The data field must be an array.',
            'data.join_policy.required' => 'The join policy field is required.',
            'data.join_policy.string' => 'The join policy must be a string.',
        ]);

        if($validator->fails())
        {
            return $this->apiResponseService->badRequest('Validation failed.', $validator->errors());
        }

        try
        {
            $join_policy = $this->areaService->set_join_policy(JoinPolicyEnum::from($request['data']['join_policy']));

            $response_data = [
                'join_policy' => $join_policy,
            ];

            return $this->apiResponseService->ok($response_data, 'join_policy has been updated successfully.');
        }
        catch (ValueError)
        {
            return $this->apiResponseService->badRequest('Invalid join policy value.');
        }
        catch (Throwable $e)
        {
            Log::error('An error occurred while updating join policy', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }
}