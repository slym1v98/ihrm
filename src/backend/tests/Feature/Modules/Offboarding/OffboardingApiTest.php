<?php

namespace Tests\Feature\Modules\Offboarding;

use App\Modules\Offboarding\Infrastructure\Persistence\Eloquent\OffboardingRequestModel;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OffboardingApiTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $response = $this->postJson('/api/v1/auth/login', ['email' => 'admin@ihrm.local', 'password' => 'password']);
        $this->token = $response->json('data.access_token');
    }

    public function test_auth_required(): void
    {
        $this->getJson('/api/v1/offboarding/requests')->assertStatus(401);
        $this->postJson('/api/v1/offboarding/requests', [])->assertStatus(401);
    }

    public function test_request_create_and_list(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/v1/offboarding/requests', [
            'employee_id' => '00000000-0000-0000-0000-000000000001',
            'type' => 'resignation',
            'reason' => 'Relocation',
            'requested_last_working_date' => '2026-08-01',
        ]);
        $response->assertCreated();
        $id = $response->json('data.id');
        $this->assertNotNull($id, 'Response: '.$response->content());

        $this->withToken($this->token)->getJson('/api/v1/offboarding/requests')->assertOk();

        // Check the model exists
        $model = OffboardingRequestModel::find($id);
        $this->assertNotNull($model, "OffboardingRequest not found in DB: $id");
    }

    public function test_submit_approve_flow(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/v1/offboarding/requests', [
            'employee_id' => '00000000-0000-0000-0000-000000000001',
            'type' => 'resignation',
            'reason' => 'Relocation',
            'requested_last_working_date' => '2026-08-01',
        ]);
        $id = $response->json('data.id');

        $submitResp = $this->withToken($this->token)->postJson("/api/v1/offboarding/requests/{$id}/submit");
        if ($submitResp->status() !== 200) {
            $this->fail('Submit failed: '.$submitResp->content());
        }

        $approveResp = $this->withToken($this->token)->postJson("/api/v1/offboarding/requests/{$id}/approve", [
            'approved_last_working_date' => '2026-08-01',
        ]);
        if ($approveResp->status() !== 200) {
            $this->fail('Approve failed: '.$approveResp->content());
        }
        $approveResp->assertOk();
    }
}
