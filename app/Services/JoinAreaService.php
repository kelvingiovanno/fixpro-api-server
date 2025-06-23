<?php

namespace App\Services;

use App\Enums\ApplicantStatusEnum;
use App\Enums\JoinPolicyEnum;
use App\Enums\MemberRoleEnum;

use App\Exceptions\JoinAreaException;

use App\Models\Applicant;
use App\Models\AuthenticationCode;
use App\Models\Member;

use App\Services\NonceCodeService;


use Exception;

class JoinAreaService 
{
    public function __construct(
        protected NonceCodeService $nonceCodeService,
        protected JoinFormService $joinFormService,
        protected AreaService $areaService,    
    ) { }

    public function request(array $form_data, string $nonce_code): Applicant
    {
        $this->nonceCodeService->check($nonce_code);

        $policy = $this->areaService->get_join_policy(); 

        if ($policy === JoinPolicyEnum::CLOSED->value) 
        {
            $this->nonceCodeService->delete($nonce_code);
            throw new JoinAreaException('Joining area is currently closed.');
        }

        $this->joinFormService->validate($form_data);
        $member_data = $this->joinFormService->normalize($form_data);

        $member = Member::create(
            array_merge(['role_id' => MemberRoleEnum::MEMBER->id()], $member_data)
        );

        if ($policy === JoinPolicyEnum::APPROVAL_NEEDED->value)
        {    
            $applicant = Applicant::create([
                'member_id' => $member->id,
                'status_id' => ApplicantStatusEnum::PENDING->id(),
            ]);

            $this->nonceCodeService->delete($nonce_code);

            return $applicant;
        }
        
        if ($policy === JoinPolicyEnum::OPEN->value)
        {
            $applicant = Applicant::create([
                'member_id' => $member->id,
                'status_id' => ApplicantStatusEnum::ACCEPTED->id(),
            ]);

            $this->nonceCodeService->delete($nonce_code);

            return $applicant;
        }

        throw new JoinAreaException('Invalid join policy configuration.');
    }

    public function check(string $application_id) : AuthenticationCode
    {
        $applicant = Applicant::findOrFail($application_id);

        $first_member_login = $this->areaService->is_first_joined();

        if(!$first_member_login)
        {
            $applicant->member()->update([
                'role_id' => MemberRoleEnum::MANAGEMENT->id(),
            ]);

            $applicant->update([
                'status_id' => ApplicantStatusEnum::ACCEPTED->id(),
            ]);

            $authentication_code = AuthenticationCode::create([
                'application_id' => $application_id,
            ]);

            $this->areaService->mark_first_joined();

            return $authentication_code;
        }
        
        if ($applicant->expires_on < now()) {
            throw new JoinAreaException('Your application has expired.');
        }

        $status_id =$applicant->status->id;

        if($status_id === ApplicantStatusEnum::PENDING->id())
        {
            throw new JoinAreaException('Your application is still pending.');
        }
        
        if($status_id === ApplicantStatusEnum::REJECTED->id())
        {
            throw new JoinAreaException('Your application has been rejected.');
        }

        if($status_id === ApplicantStatusEnum::ACCEPTED->id())
        {
            $authentication_code = AuthenticationCode::create([
                'application_id' => $application_id,
            ]);

            return $authentication_code;
        }

        throw new Exception('Unknown application status.');
    }
}
