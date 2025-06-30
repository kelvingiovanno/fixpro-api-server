<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;

use App\Enums\ApplicantStatusEnum;
use App\Enums\MemberCapabilityEnum;
use App\Enums\MemberRoleEnum;
use App\Models\Applicant;
use App\Models\Member;

use App\Services\ApiResponseService;
use App\Services\AreaService;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;
use ValueError;

class ApplicantController extends Controller
{
    public function __construct(
        protected ApiResponseService $apiResponseService,
        protected AreaService $areaService,
    ) { }

    public function index()
    {
        try 
        {
            $members = Member::whereHas('applicant', function ($query) {
                $query->where('status_id', ApplicantStatusEnum::PENDING->id());
            })->get();

            $form_fields = $this->areaService->get_join_form();

            $response_data = $members->map(function ($member) use ($form_fields) {
                return [
                    'id' => $member->applicant->id, 
                    'form_answer' => collect($form_fields)->map(function ($field) use ($member) {
                        return [
                            'field_label' => $field,
                            'field_value' => $member->$field,
                        ];
                    })->toArray(),
                    'submitted_on' => $member->member_since->format('Y-m-d\TH:i:sP'),
                ];
            });

            return $this->apiResponseService->ok($response_data , 'Successfully retrieved applicants.');
        } 
        catch (Throwable $e) 
        {
            Log::error('An error occured while retrieving pending applicants', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        
            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }
    
    public function show(string $application_id)
    {
        $validator = Validator::make(['application_id' => $application_id], [
            'application_id' => 'required|string|uuid',
        ],
        [
            'application_id.required' => 'The application ID is required.',
            'application_id.uuid'     => 'The application ID must be a valid UUID.',
        ]);

        if($validator->fails())
        {
            return $this->apiResponseService->badRequest('Validation Failed.', $validator->errors());
        }

        try 
        {
            $applicant = Applicant::with('member')->findOrFail($application_id);

            $form_fields = $this->areaService->get_join_form();

            $response_data = [
                'id' => $applicant->member->id,
                'form_answer' => collect($form_fields)->map(function ($field) use ($applicant) {
                    return [
                        'field_label' => $field,
                        'field_value' => $applicant->member->$field,
                    ];
                })->toArray(),
                'submitted_on' => $applicant->member->member_since->format('Y-m-d\TH:i:sP'),
            ];
    
            return $this->apiResponseService->ok($response_data, 'Successfully retrieved applicant.');
        } 
        catch (ModelNotFoundException)
        {
            return $this->apiResponseService->notFound('Applicant not found.');
        }
        catch (Throwable $e) 
        {
            Log::error('An error occurred while retrieving applicant', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        
            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }

    public function accept(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.application_id' => 'required|uuid',
            'data.role' => 'required|string',
            'data.specialization' => 'nullable|array',
            'data.specialization.*' => 'string|uuid|exists:ticket_issue_types,id',
            'data.capabilities' => 'nullable|array',
            'data.capabilities.*' => 'string|exists:member_capabilities,name',
            'data.title' => 'nullable|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return $this->apiResponseService->badRequest("Validation failed.", $validator->errors());
        }
        
        try 
        {
            $applicant = Applicant::with('member')->findOrFail($request->data['application_id']);

            $applicant->update([
                'status_id' => ApplicantStatusEnum::ACCEPTED->id(),
            ]);

            $applicant->member()->update([
                'role_id' => MemberRoleEnum::from($request->data['role'])->id(),
                'title' => $request->data['title'],
            ]);
            
            $capabilityIds = array_map(
                fn($cap) => MemberCapabilityEnum::from($cap)->id(),
                $request->data['capabilities']
            );

            $applicant->member->specialities()->attach($request->data['specialization']);
            $applicant->member->capabilities()->attach($capabilityIds);

            $form_fields = $this->areaService->get_join_form();

            $response_data = [
                'id' => $applicant->member->id,
                'form_answer' => collect($form_fields)->map(function ($field) use ($applicant) {
                    return [
                        'field_label' => $field,
                        'field_value' => $applicant->member->$field,
                    ];
                })->toArray(),
            ];

            return $this->apiResponseService->ok($response_data, 'Applicant Acccpted');
        } 
        catch (ModelNotFoundException)
        {
            return $this->apiResponseService->notFound('Applicant not found.');
        }
        catch (ValueError)
        {
            return $this->apiResponseService->badRequest('Invalid role provided.');
        }
        catch (Throwable $e) 
        { 
            Log::error('An error occurred during applicant acceptance', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }
    
    public function reject(string $application_id)
    {
        $validator = Validator::make(['application_id' => $application_id], [
            'application_id' => 'required|string|uuid',
        ],
        [
            'application_id.required' => 'The application ID is required.',
            'application_id.uuid'     => 'The application ID must be a valid UUID.',
        ]);

        if($validator->fails())
        {
            return $this->apiResponseService->badRequest('Validation Failed.', $validator->errors());
        }

        try 
        {
            $applicant = Applicant::findOrFail($application_id);
    
            JWTAuth::invalidate(JWTAuth::getToken());
            
            $applicant->update(['status_id' => ApplicantStatusEnum::REJECTED->id()]);
            
            $applicant->member->delete();

            return $this->apiResponseService->ok('Applicant rejected successfully.');
        } 
        catch (ModelNotFoundException)
        {
            return $this->apiResponseService->notFound('Applicant not found.');
        }
        catch (Throwable $e) 
        {
            Log::error('An error occurred while rejecting the applicant', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        
            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }
}
