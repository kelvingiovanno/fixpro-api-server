<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index()
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

    public function show(string $_memberId)
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
    
    public function destroy(string $_memberId)
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

    public function update(string $_memberId)
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
}