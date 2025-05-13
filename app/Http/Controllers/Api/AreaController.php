<?php  

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Enums\ApplicantStatusEnum;
use App\Enums\IssueTypeEnum;
use App\Enums\MemberRoleEnum;

use App\Models\Enums\MemberCapability;
use App\Models\Enums\TicketIssueType;

use App\Models\Applicant;
use App\Models\Member;
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
                'issue_type_count' => TicketIssueType::all()->count(),
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
    
    public function getJoinPolicy()
    {
        try
        {
            $join_policy = SystemSetting::get('area_join_policy');

            if (!$join_policy)
            {
                return $this->apiResponseService->noContent('join_policy has not been set.');
            }

            $response_data = [
                'join_policy' => $join_policy,
            ];

            return $this->apiResponseService->ok($response_data, 'join_policy retrieved successfully.');
        }
        catch(Throwable $e)
        {
            Log::error('Failed to retrieve join policy', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('Failed to retrieve join policy');
        }
    }

    public function putJoinPolicy(Request $_request)
    {
        $input = $_request->input('data.join_policy');

        if(!$input)
        {
            return $this->apiResponseService->badRequest('The join_policy field is required.');
        }

        try
        {
            SystemSetting::put('area_join_policy', $input);

            $join_policy = SystemSetting::get('area_join_policy');

            $response_data = [
                'join_policy' => $join_policy,
            ];

            return $this->apiResponseService->ok($response_data, 'join_policy has been updated successfully.');
        }
        catch (Throwable $e)
        {
            Log::error('Failed to update join policy', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('Failed to update join policy');
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
            
            return $this->apiResponseService->ok('Referral code successfully deleted.');
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
            $members = Member::whereHas('applicant', function ($query) {
                $query->where('status_id', ApplicantStatusEnum::ACCEPTED->id());
            })->get();

            $data = $members->map(function ($member) {

                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'role' => $member->role->name,
                    'title' => $member->title,
                    'specialties' => $member->specialities->map(function ($speciality) {
                        return [
                            'id' => $speciality->id,
                            'name' => $speciality->name,
                            'service_level_agreement_duration_hour' => $speciality->sla_duration_hour ?? 'Not assigned yet',
                        ];
                    }),
                    'capabilities' => $member->capabilities->map(function ($capability) {
                        return $capability->name;
                    }),
                    'member_since' => $member->member_since,
                    'member_until' => $member->member_until,
                ];
            });

            return $this->apiResponseService->ok($data, 'Successfully retrieve member.');
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

    public function getMember(string $_memberId)
    {
        if (!Str::isUuid($_memberId)) {
            return $this->apiResponseService->badRequest('Member not found.');
        }

        try 
        {
            $member = Member::with(['role', 'specialities'])->find($_memberId);
    
            if (!$member) {
                return $this->apiResponseService->notFound('Member not found.');
            }
    
            $response_data = [
                'id' => $member->id,
                'name' => $member->name,
                'role' => $member->role->name,
                'title' => $member->title,
                'specialties' => $member->specialities->map(function ($speciality) {
                    return [
                        'id' => $speciality->id,
                        'name' => $speciality->name,
                        'service_level_agreement_duration_hour' => $speciality->sla_duration_hour ?? 'Not assigned yet',
                    ];
                }),
                'capabilities' => $member->capabilities->map(function ($capability) {
                    return $capability->name;
                }),
                'member_since' => $member->member_since,
                'member_until' => $member->member_until,
            ];
    
            return $this->apiResponseService->ok($response_data, 'Successfully retrieve member');
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
    
    public function deleteMember(string $_memberId)
    {
        if (!Str::isUuid($_memberId)) {
            return $this->apiResponseService->badRequest('Member not found.');
        }

        try 
        {
            $member = Member::find($_memberId);
    
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

    public function putMember(string $_memberId)
    {
        if (!Str::isUuid($_memberId)) {
            return $this->apiResponseService->badRequest('Member not found.');
        }

        $data = request()->input('data');

        if(!$data)
        {
            return $this->apiResponseService->unprocessableEntity('Missing required data payload.');
        }

        $validator = Validator::make($data, [
            'id' => 'required|uuid|exists:members,id',
            'name' => 'required|string',
            'role' => 'required|string|exists:member_roles,name',
            'title' => 'required|string',
            'specialties' => 'nullable|array',
            'specialties.*.id' => 'required|uuid|exists:ticket_issue_types,id',
            'specialties.*.name' => 'required|string',
            'specialties.*.service_level_agreement_duration_hour' => 'required|integer',
            'capabilities' => 'nullable|array',
            'capabilities.*' => 'required|string|exists:member_capabilities,name',
            'member_since' => 'required|date',
            'member_until' => 'required|date|after_or_equal:member_since',
        ]);

        if ($validator->fails()) {
            return $this->apiResponseService->unprocessableEntity('There was an issue with your input', $validator->errors());
        }

        try {
            $member = Member::find($_memberId);

            if (!$member) {
                return $this->apiResponseService->notFound('Member not found.');
            }

            $member->update([
                'name' => $data['name'],
                'role_id' => MemberRoleEnum::idFromName($data['role']),
                'title' => $data['title'],
                'member_since' => $data['member_since'],
                'member_until' => $data['member_until'],
            ]);

            $specialtiesIds = collect($data['specialties'])->pluck('id');
            $member->specialities()->sync($specialtiesIds); 

            $capabilityIds = MemberCapability::whereIn('name', $data['capabilities'])->pluck('id');
            $member->capabilities()->sync($capabilityIds);

            $new_member = Member::find($_memberId);

            $response_data = [
                'id' => $new_member->id,
                'name' => $new_member->name,
                'role' => $new_member->role->name,
                'title' => $new_member->title,
                'specialties' => $new_member->specialities->map(function ($speciality) {
                    return [
                        'id' => $speciality->id,
                        'name' => $speciality->name,
                        'service_level_agreement_duration_hour' => $speciality->sla_duration_hour ?? 'Not assigned yet',
                    ];
                }),
                'capabilities' => $new_member->capabilities->map(function ($capability) {
                    return $capability->name;
                }),
                'member_since' => $new_member->member_since,
                'member_until' => $new_member->member_until,
            ];

            return $this->apiResponseService->ok($response_data);

        } 
        catch (Throwable $e) {
            Log::error('Failed to update member', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('Failed to update member.');
        }
    }
    
    public function getPendingMembers()
    {
        try 
        {
            $members = Member::whereHas('applicant', function ($query) {
                $query->where('status_id', ApplicantStatusEnum::PENDING->id());
            })->get();

            $formFields = json_decode(SystemSetting::get('area_join_form'), true);

            $response_data = $members->map(function ($member) use ($formFields) {
                return [
                    'id' => (string) Str::uuid(), 
                    'form_answer' => collect($formFields)->map(function ($field) use ($member) {
                        return [
                            'field_label' => $field,
                            'field_value' => $member->$field,
                        ];
                    })->toArray(),
                ];
            });

            return $this->apiResponseService->ok($response_data , 'Successfully retrieved pending members.');
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
    
    public function postPendingMembers(Request $_request)
    {
        $validator = Validator::make($_request->input('data'), [
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
            $applicationId = $_request->input('data.application_id');
            $role = MemberRoleEnum::idFromName($_request->input('data.role'));
            $specializationLabels = $_request->input('data.specialization');
            $title = $_request->input('data.title');

            if (!$role) {
                return $this->apiResponseService->unprocessableEntity('Invalid role provided.');
            }

            if($specializationLabels)
            {
                $specializationIds = array_map(function ($label) {
                    $id = IssueTypeEnum::idFromName($label);
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

            $applicant->update([
                'status_id' => ApplicantStatusEnum::ACCEPTED->id(),
                'role_id' => $role,
                'title' => $title,
            ]);

            $member = $applicant->member;

            if($specializationLabels)
            {
                $member->specialities()->attach($specializationIds);
            }

            $formFields = json_decode(SystemSetting::get('area_join_form'), true);

            $response_data = [
                'id' => $member->id,
                'form_answer' => collect($formFields)->map(function ($field) use ($member) {
                    return [
                        'field_label' => $field,
                        'field_value' => $member->$field,
                    ];
                })->toArray(),
            ];

            return $this->apiResponseService->created($response_data, 'Applicant Acccpted');
        } 
        catch (Throwable $e) 
        { 
            Log::error('Failed to create member', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('An error occurred while creating the member.');
        }
    }

    public function getPendingMember(string $_applicationId)
    {
        if (!Str::isUuid($_applicationId)) {
            return $this->apiResponseService->badRequest('Applicant not found.');
        }

        try 
        {
            $member = Applicant::find($_applicationId)->member;
    
            if (!$member) {
                return $this->apiResponseService->notFound('Applicant not found.');
            }

            $formFields = json_decode(SystemSetting::get('area_join_form'), true);

            $response_data = [
                'id' => $member->id,
                'form_answer' => collect($formFields)->map(function ($field) use ($member) {
                    return [
                        'field_label' => $field,
                        'field_value' => $member->$field,
                    ];
                })->toArray(),
            ];
    
            return $this->apiResponseService->ok($response_data, 'Successfully retrieved pending member.');
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
    
    public function delPendingMember(string $_applicationId)
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
    
            $applicant->update(['status_id' => ApplicantStatusEnum::REJECTED->id()]);
    
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