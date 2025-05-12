<?php

namespace Tests\Feature;

use Tests\TestCase;

use App\Enums\ApplicantStatusEnum;

use App\Models\Applicant;
use App\Models\Member;
use App\Models\SystemSetting;


use App\Services\NonceCodeService;
use App\Services\ReferralCodeService;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class EntryControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private ReferralCodeService $referralCodeService;
    private NonceCodeService $nonceCodeService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed');

        SystemSetting::put('area_join_form', json_encode(['name','email', 'phone_number']));
        SystemSetting::put('area_name', 'Binus Kemanggisas');
        SystemSetting::put('area_name', 'approval-needed');

        $this->referralCodeService = app(ReferralCodeService::class);
        $this->nonceCodeService = app(NonceCodeService::class);
    }

    public function test_get_form()
    {
        $referralCode = $this->referralCodeService->getReferral();

        $response = $this->getJson('/api/entry/form?area_join_form_referral_tracking_identifier=' . $referralCode);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'message',
            'data' => [
                'area_name',
                'form_fields' => [
                    '*' => [
                        'field_label',
                    ],
                ],
                'nonce',
            ],
            'errors',
        ]);

        $data = $response->json('data');

        $this->assertNotEmpty($data['area_name']);
        $this->assertNotEmpty($data['form_fields']);

        foreach ($data['form_fields'] as $field) {
            $this->assertNotEmpty($field['field_label']);
        }

        $this->assertNotEmpty($data['nonce']);
    }

    public function test_submit()
    {
        $nonce_code = $this->nonceCodeService->generateNonce();

        $get_form = json_decode(SystemSetting::get('area_join_form'), true);

        $payload = [
            'data' => []
        ];

        foreach ($get_form as $field) {
            $formatted_label = ucwords(str_replace('_', ' ', $field)); 
            
            $payload['data'][] = [
                'field_label' => $formatted_label,
                'field_value' => 'dummy_' . strtolower($formatted_label), 
            ];
        }

        $response = $this->postJson('/api/entry/form?area_join_form_submission_nonce=' . $nonce_code, $payload);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'message',
            'data' => [
                'application_id',
                'application_expiry_date',
            ],
            'errors',
        ]);

        $data = $response->json('data');

        $this->assertNotEmpty($data['application_id']);
        $this->assertNotEmpty($data['application_expiry_date']);

        $this->assertDatabaseHas('applicants', [
            'id' => $data['application_id'],
        ]);

        $member_id = Applicant::find($data['application_id'])->member_id;

        $converted = [];

        foreach ($payload['data'] as $item) {
            $key = strtolower(str_replace(' ', '_', $item['field_label']));
            $converted[$key] = $item['field_value'];
        }

        $this->assertDatabaseHas('members', array_merge(['id' => $member_id], $converted));
    }

    public function test_check()
    {
        $member = Member::factory()->create();

        $applicant = Applicant::factory()->create(['member_id' => $member->id]);

        $applicant->update([
            'status_id' => ApplicantStatusEnum::ACCEPTED->id(),
        ]);

        $payload = [
            'data' => [
                'application_id' => $applicant->id,
            ],
        ];

        $response = $this->postJson('/api/entry/check', $payload);

        $response->assertExactJsonStructure([
            'message',
            'data' => [
                'authentication_code',
            ],
            'errors',
        ]);

        $data = $response->json('data');


        $this->assertNotEmpty($data['authentication_code']);

        $auth_code_id = Applicant::find($applicant->id)->authentication_code->id;

        $this->assertDatabaseHas('authentication_codes', [
            'id' => $auth_code_id,
        ]);
    }
}
 