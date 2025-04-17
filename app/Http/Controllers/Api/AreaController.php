<?php  

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\Applicant;

use App\Services\EntryService;
use App\Services\ApiResponseService;

use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Str;

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

    public function postMembers(Request $request)
    {
        
    }

    public function getMember(string $member_id)
    {
        if (!Str::isUuid($member_id)) {
            return $this->apiResponseService->badRequest('Member not found.');
        }
        
        $member = User::with(['userData', 'role', 'specialities'])->find($member_id);
    
        if (!$member) {
            return $this->apiResponseService->notFound('Member not found.');
        }
        
        $userData = $member->userData;

        unset($userData['id'], $userData['user_id']);

        $data = [
            'id' => $member->id,
            'role' => $member->role->label ?? null,
            'speciality' => $member->specialities->pluck('label')->toArray(),
            'member_since' => $member->member_since,
            'member_until' => $member->member_until,
            'user_data' => $userData,
        ];
    
        return $this->apiResponseService->ok($data);
    }

    public function delMember(string $member_id)
    {
        if (!Str::isUuid($member_id)) {
            return $this->apiResponseService->badRequest('Member not found.');
        }

        $member = User::find($member_id);

        if (!$member) {
            return $this->apiResponseService->notFound('Member not found.');
        }

        $member->delete();

        return $this->apiResponseService->ok('Member deleted successfully.');
    }

    public function getPendingMembers()
    {
        $pendingMembers = Applicant::all();

        return $this->apiResponseService->ok($pendingMembers);
    }

    public function getApplicant(string $application_id)
    {
        if (!Str::isUuid($application_id)) {
            return $this->apiResponseService->badRequest('Aplicant not found.');
        }

        $applicant = Applicant::find($application_id);

        if (!$applicant) {
            return $this->apiResponseService->notFound('Applicant not found.');
        }

        return $this->apiResponseService->ok($applicant);
    }

    public function delApplicant(string $application_id)
    {
        if (!Str::isUuid($application_id)) {
            return $this->apiResponseService->badRequest('Applicant not found.');
        }

        $applicant = Applicant::find($application_id);

        if (!$applicant) {
            return $this->apiResponseService->notFound('Applicant not found.');
        }

        $applicant->delete();

        return $this->apiResponseService->ok('Applicant deleted successfully.');
    }

}