<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PendingMemberController extends Controller
{
    public function index()
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
    
    public function accept(Request $_request)
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

    public function show(string $_applicationId)
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
    
    public function reject(string $_applicationId)
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
