<?php

namespace App\Services;

use App\Enums\ApplicantStatusEnum;
use App\Enums\JoinPolicyEnum;
use App\Enums\MemberRoleEnum;
use App\Exceptions\JoinPolicyException;

use App\Models\SystemSetting;
use App\Models\Applicant;
use App\Models\Member;
use App\Services\NonceCodeService;

class JoinPolicyService 
{
    private NonceCodeService $nonceCodeService;
    private JoinFormService $joinFormService;

    public function __construct(
        NonceCodeService $_nonceCodeService,
        JoinFormService $_joinFormService,    
    )
    {
        $this->nonceCodeService = $_nonceCodeService;
        $this->joinFormService = $_joinFormService;
    }

    public function set(JoinPolicyEnum $policy): void
    {
        SystemSetting::put('area_join_policy', $policy->value);
    }

    public function check(array $form_data, string $nonce_code): Applicant
    {
        $this->nonceCodeService->check($nonce_code);

        $policy = SystemSetting::get('area_join_policy'); 

        if ($policy === JoinPolicyEnum::CLOSED->value) 
        {
            $this->nonceCodeService->delete($nonce_code);
            throw new JoinPolicyException('Joining area is currently closed.');
        }

        $this->joinFormService->validate($form_data);
        $member_data = $this->joinFormService->normalize($form_data);

        $member = Member::create(
            array_merge(['role_id' => MemberRoleEnum::MEMBER->id()], $member_data)
        );
        
        if ($policy === JoinPolicyEnum::APROVAL_NEEDED->value)
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

        throw new JoinPolicyException('Invalid join policy configuration.');
    }
}
