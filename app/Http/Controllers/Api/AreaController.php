<?php  

namespace App\Http\Controllers\Api;

use App\Enums\ApplicantStatusEnum;
use App\Http\Controllers\Controller;

use App\Enums\UserRoleEnum;
use App\Enums\UserSpeciallityEnum;

use App\Models\User;
use App\Models\UserData;
use App\Models\Applicant;
use App\Models\AuthenticationCode;
use App\Models\Enums\ApplicantStatus;
use App\Models\SystemSetting;
use App\Services\ApiResponseService;
use App\Services\ReferralCodeService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use Throwable;

class AreaController extends Controller
{
    private ReferralCodeService $referralCodeService;
    private ApiResponseService $apiResponseService;
    
    public function __construct (
        ReferralCodeService $_referralCodeService, 
        ApiResponseService $_apiResponseService,
    ) {
        $this->referralCodeService = $_referralCodeService;
        $this->apiResponseService = $_apiResponseService;
    }
    
    public function index()
    {
        try 
        {
            $reponse_data = [
                'name' => SystemSetting::get('area_name'),
                'join_policy' => SystemSetting::get('area_join_policy'),
                'member_count' => Applicant::where('status_id', ApplicantStatusEnum::ACCEPTED->id())->count(),
                'pending_member_count' => Applicant::where('status_id', ApplicantStatusEnum::PENDING->id())->count(), 
            ];

            return $this->apiResponseService->ok($reponse_data, '');
        } 
        catch (Throwable $e) 
        {
            Log::error('Failed to retrieve area data', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('Failed to retrieve area data');
        }
    }

    public function getJoinCode()
    {
        try 
        {
            $endpoint = env('APP_URL');
            $refferal = $this->referralCodeService->getReferral();

            $data = [
                "endpoint" => $endpoint,
                "referral_tracking_identifier" => $refferal,
            ];

            return $this->apiResponseService->ok($data, "The string representation of the Area Join Code, which then can be transformed into a qr-code form.");
        } 
        catch (Throwable $e) 
        {
            Log::error('Failed to retrieve join code', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('Failed to retrieve join code');
        }
    }

    public function delJoinCode()
    {
        try 
        {
            $this->referralCodeService->deleteReferral();
            return $this->apiResponseService->ok();
        } 
        catch (Throwable $e) 
        {
            Log::error('Failed to delete join code', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
           
            return $this->apiResponseService->internalServerError('Failed to delete join code');
        }
    }

    public function getMembers()
    {
        try 
        {
            $users = User::with(['userData', 'role'])->get();

            $data = $users->map(function ($user) {

                $user_data = $user->userData;
                unset($user_data['id'], $user_data['user_id']);

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->role->label,
                    'speciality' => $user->specialities->pluck('label')->toArray(),
                    'title' => $user->title,
                    'member_since' => $user->member_since,
                    'member_until' => $user->member_until,
                    'more_information' => $user_data,
                ];
            });

            return $this->apiResponseService->ok($data);
        } 
        catch (Throwable $e) 
        {
            Log::error('Failed to retrieve members', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        
            return $this->apiResponseService->internalServerError('Failed to retrieve members');
        }
    }

    public function postMembers(Request $_request)
    {
        $validator = Validator::make($_request->all(), [
            'application_id' => 'required|uuid|exists:applicants,id',
            'role' => 'required|string',
            'specialization' => 'nullable|array',
            'specialization.*' => 'string',
            'title' => 'required|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return $this->apiResponseService->unprocessableEntity("There was an issue with your input", $validator->errors());
        }
        
        try 
        {
            $applicationId = $_request->input('application_id');
            $role = UserRoleEnum::idFromLabel($_request->input('role'));
            $specializationLabels = $_request->input('specialization');
            $title = $_request->input('title');

            if (!$role) {
                return $this->apiResponseService->unprocessableEntity('Invalid role provided.');
            }

            if($specializationLabels)
            {
                $specializationIds = array_map(function ($label) {
                    $id = UserSpeciallityEnum::idFromLabel($label);
                    if (!$id) {
                        return null; 
                    }
                    return $id;
                }, $specializationLabels);
            
                
                if (in_array(null, $specializationIds, true)) {
                    return $this->apiResponseService->unprocessableEntity('One or more specializations are invalid.');
                }
            }

            $applicant = Applicant::find($applicationId);

            if (!$applicant) {
                return $this->apiResponseService->notFound('Applicant not found');
            }

            $applicant->update(['status_id' => ApplicantStatusEnum::ACCEPTED->id()]);

            $user = User::create([
                'role_id' => $role,
                'name' => $applicant->name,
                'title' => $title,
            ]);

            if($specializationLabels)
            {
                $user->specialities()->attach($specializationIds);
            }


            $applicantData = $applicant->toArray();
            unset($applicantData['name'], $applicantData['status_id'], $applicantData['id'], $applicantData['expires_at']);

            $userData = UserData::create(array_merge($applicantData, [
                'user_id' => $user->id,
            ]));
            
            $applicant->update(['is_accepted' => true]);

            AuthenticationCode::create([
                'applicant_id' => $applicant->id,
                'user_id' => $user->id,
            ]);

            unset($userData['id'], $userData['user_id']);

            $data = [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role->label,
                'speciality' => $user->specialities->pluck('label')->toArray(),
                'title' => $user->title,
                'member_since' => $user->member_since,
                'member_until' => $user->member_until,
                'more_information' => $userData
            ];

            return $this->apiResponseService->created($data, 'User created');
        } 
        catch (Throwable $e) 
        { 
            Log::error('Failed to create member', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('An error occurred while creating the user.');
        }
    }

    public function getMember(string $_memberId)
    {
        if (!Str::isUuid($_memberId)) {
            return $this->apiResponseService->badRequest('Member not found.');
        }

        try 
        {
            $member = User::with(['userData', 'role', 'specialities'])->find($_memberId);
    
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
        catch (Throwable $e) 
        {
            Log::error('Failed to retrieve member', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        
            return $this->apiResponseService->internalServerError('Failed to retrieve member.');
        }
    }
    
    public function delMember(string $_memberId)
    {
        if (!Str::isUuid($_memberId)) {
            return $this->apiResponseService->badRequest('Member not found.');
        }

        try 
        {
            $member = User::find($_memberId);
    
            if (!$member) {
                return $this->apiResponseService->notFound('Member not found.');
            }
    
            $member->delete();
    
            return $this->apiResponseService->ok('Member deleted successfully.');
        } 
        catch (Throwable $e) 
        {
            Log::error('Failed to delete member', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        
            return $this->apiResponseService->internalServerError('Failed to delete member.');
        }
    }
    
    public function getPendingMembers()
    {
        try 
        {
            $pendingMembers = ApplicantStatus::find(ApplicantStatusEnum::PENDING->id())->applicants;
    
            $response_data = $pendingMembers->map(function ($pendingMember) {
                return [
                    'id' => $pendingMember->id,
                    'status' => $pendingMember->status->label,
                    'name' => $pendingMember->name,
                    'expires_at' => $pendingMember->expires_at,
                    'email' => $pendingMember->email,
                    'phone_number' => $pendingMember->phone_number,
                    'whatsapp_registered_number' => $pendingMember->whatsapp_registered_number,
                ];
            });    

            return $this->apiResponseService->ok($response_data);
        } 
        catch (Throwable $e) 
        {
            Log::error('Failed to retrieve pending members', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        
            return $this->apiResponseService->internalServerError('Failed to retrieve pending members.');
        }
    }
    
    public function getApplicant(string $_applicationId)
    {
        if (!Str::isUuid($_applicationId)) {
            return $this->apiResponseService->badRequest('Applicant not found.');
        }

        try 
        {
            $applicant = Applicant::find($_applicationId);
    
            if (!$applicant) {
                return $this->apiResponseService->notFound('Applicant not found.');
            }
    
            return $this->apiResponseService->ok($applicant);
        } 
        catch (Throwable $e) 
        {
            Log::error('Failed to retrieve applicant', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        
            return $this->apiResponseService->internalServerError('Failed to retrieve applicant.');
        }
    }
    
    public function delApplicant(string $_applicationId)
    {
        if (!Str::isUuid($_applicationId)) {
            return $this->apiResponseService->badRequest('Applicant not found.');
        }

        try 
        {
            $applicant = Applicant::find($_applicationId);
    
            if (!$applicant) {
                return $this->apiResponseService->notFound('Applicant not found.');
            }
    
            $applicant->update(['status_id' => ApplicantStatusEnum::REJECTED->value]);
    
            return $this->apiResponseService->ok('Applicant rejected successfully.');
        } 
        catch (Throwable $e) 
        {
            Log::error('Failed to delete applicant', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        
            return $this->apiResponseService->internalServerError('Failed to delete applicant.');
        }
    }
}