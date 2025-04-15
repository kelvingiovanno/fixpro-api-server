<?php  

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\User;

use App\Services\EntryService;
use App\Services\ApiResponseService;


class AreaController extends Controller
{

    private EntryService $entryService;
    private ApiResponseService $apiResponseService;

    public function __construct(EntryService $_entryService, ApiResponseService $_apiResponseService)
    {
        $this->entryService = $_entryService;
        $this->apiResponseService = $_apiResponseService;
    }
    
    public function index()
    {

    }

    public function getJoinCode()
    {
        $endpoint = env('APP_URL');
        $refferal = $this->entryService->getReferral();

        $data = [
            "endpoint" => $endpoint,
            "referral_tracking_identifier" => $refferal,
        ];

        
        return $this->apiResponseService->ok($data);
    }

    public function delJoinCode()
    {  
        $this->entryService->deleteReferral();
        return $this->apiResponseService->ok();
    }

    public function getMembers()
    {
        $members = User::with(['userData', 'role'])->get();

        $data = $members->map(function ($member) {

            $user_data = $member->userData;
            unset($user_data['id'], $user_data['user_id']);

            return [
                'id' => $member->id,
                'role' => optional($member->role)->label,
                'speciality' => $member->specialities->pluck('label')->toArray(),
                'member_since' => $member->member_since,
                'member_until' => $member->member_until,
                'user_data' => $user_data,
            ];
        });
        
        return $this->apiResponseService->ok($data);
    }

    public function postMembers()
    {

    }

    public function getMember(int $member_id)
    {

    }

    public function delMember(int $member_id)
    {

    }

    public function getPendingMembers()
    {

    }

    public function getApplicant(string $application_id)
    {
        
    }

    public function delApplicant(string $application_id)
    {

    }
}