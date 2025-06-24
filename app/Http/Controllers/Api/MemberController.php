<?php

namespace App\Http\Controllers;

use App\Enums\ApplicantStatusEnum;
use App\Enums\MemberRoleEnum;

use App\Models\Enums\MemberCapability;

use App\Models\Member;

use App\Services\ApiResponseService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use Illuminate\Database\Eloquent\ModelNotFoundException;

use Throwable;
use ValueError;

class MemberController extends Controller
{
    public function __construct(
        protected ApiResponseService $apiResponseService,
    ) { }

    public function index()
    {
        try 
        {
            $members = Member::whereHas('applicant', function ($query) {
                $query->where('status_id', ApplicantStatusEnum::ACCEPTED->id());
            })->get();

            $response_data = $members->map(function ($member) {

                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'role' => $member->role->name,
                    'title' => $member->title,
                    'specialties' => $member->specialities->map(function ($speciality) {
                        return [
                            'id' => $speciality->id,
                            'name' => $speciality->name,
                            'service_level_agreement_duration_hour' => $speciality->sla_hours ,
                        ];
                    }),
                    'capabilities' => $member->capabilities->map(function ($capability) {
                        return $capability->name;
                    }),
                    'member_since' => $member->member_since,
                    'member_until' => $member->member_until,
                ];
            });

            return $this->apiResponseService->ok($response_data, 'Successfully retrieved all accepted members.');
        } 
        catch (Throwable $e) 
        {
            Log::error('An error occurred while retrieving the accepted members', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        
            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }

    public function show(string $member_id)
    {
        $validator = Validator::make(['member_id' => $member_id], [
            'member_id' => 'required|uuid'
        ],
        [
            'member_id.required' => 'The member ID is required.',
            'member_id.uuid'     => 'Invalid member ID format.',
        ]);

        if($validator->fails())
        {
            return $this->apiResponseService->badRequest('Validation failed.', $validator->errors());
        }

        try 
        {
            $member = Member::with(['role', 'specialities', 'capabilities'])->findOrFail($member_id);
    
            $response_data = [
                'id' => $member->id,
                'name' => $member->name,
                'role' => $member->role->name,
                'title' => $member->title,
                'specialties' => $member->specialities->map(function ($speciality) {
                    return [
                        'id' => $speciality->id,
                        'name' => $speciality->name,
                        'service_level_agreement_duration_hour' => $speciality->sla_hours,
                    ];
                }),
                'capabilities' => $member->capabilities->map(function ($capability) {
                    return $capability->name;
                }),
                'member_since' => $member->member_since,
                'member_until' => $member->member_until,
            ];
    
            return $this->apiResponseService->ok($response_data, 'Successfully retrieved member');
        } 
        catch (ModelNotFoundException)
        {
            return $this->apiResponseService->notFound('Member not found.');
        }
        catch (Throwable $e) 
        {
            Log::error('An error occurred while retrieving member', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        
            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }
    
    public function destroy(string $member_id)
    {
        $validator = Validator::make(['member_id' => $member_id], [
            'member_id' => 'required|uuid'
        ],
        [
            'member_id.required' => 'The member ID is required.',
            'member_id.uuid'     => 'Invalid member ID format.',
        ]);

        if($validator->fails())
        {
            return $this->apiResponseService->badRequest('Validation failed.', $validator->errors());
        }

        try 
        {
            $member = Member::findOrFail($member_id);

            $member->delete();
    
            return $this->apiResponseService->ok('Member revoked successfully.');
        } 
        catch(ModelNotFoundException)
        {
            return $this->apiResponseService->notFound('Member not found.');
        }
        catch (Throwable $e) 
        {
            Log::error('An error occurred during member revocation', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        
            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }

    public function update(Request $request, string $member_id)
    {
        $validator = Validator::make(array_merge($request->all(), ['member_id' => $member_id]), [
            'member_id' => 'required|uuid',
            'data' => 'required|array',
            'data.id' => 'required|uuid',
            'data.name' => 'required|string',
            'data.role' => 'required|string',
            'data.title' => 'required|string',
            'data.specialties' => 'nullable|array',
            'data.specialties.*.id' => 'required|uuid|exists:ticket_issue_types,id',
            'data.specialties.*.name' => 'required|string|exists:ticket_issue_types,name',
            'data.specialties.*.service_level_agreement_duration_hour' => 'required|integer|exists:ticket_issue_types,sla_hours',
            'data.capabilities' => 'nullable|array',
            'data.capabilities.*' => 'required|string|exists:member_capabilities,name',
            'data.member_since' => 'required|date',
            'data.member_until' => 'required|date|after_or_equal:member_since',

        ]);

        if($validator->fails())
        {
            return $this->apiResponseService->badRequest('Validation failed.', $validator->errors());
        }

        try 
        {
            $member = Member::findOrFail($member_id);

            $member->update([
                'name' => $request->data['name'],
                'role_id' => MemberRoleEnum::from($request->data['role']),
                'title' => $request->data['title'],
                'member_since' => $request->data['member_since'],
                'member_until' => $request->data['member_until'],
            ]);

            $specialty_ids = collect($request->data['specialties'])->pluck('id');
            $capability_ids = MemberCapability::whereIn('name', $request->data['capabilities'])->pluck('id');
            
            $member->specialities()->sync($specialty_ids); 
            $member->capabilities()->sync($capability_ids);

            $response_data = [
                'id' => $member->id,
                'name' => $member->name,
                'role' => $member->role->name,
                'title' => $member->title,
                'specialties' => $member->specialities->map(function ($speciality) {
                    return [
                        'id' => $speciality->id,
                        'name' => $speciality->name,
                        'service_level_agreement_duration_hour' => $speciality->sla_hours,
                    ];
                }),
                'capabilities' => $member->capabilities->map(function ($capability) {
                    return $capability->name;
                }),
                'member_since' => $member->member_since,
                'member_until' => $member->member_until,
            ];

            return $this->apiResponseService->ok($response_data, 'Successfully updated member.');

        } 
        catch (ModelNotFoundException)
        {
            return $this->apiResponseService->notFound('Member not found.');
        }
        catch (ValueError)
        {
            return $this->apiResponseService->badRequest('Invalid role provided.');
        }
        catch (Throwable $e) 
        {

            Log::error('An error occured while updating member', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }
}