<?php

namespace Tests\Feature\Modules\Recruitment;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\Organization\Infrastructure\Persistence\Eloquent\DepartmentModel;
use Tests\TestCase;

class RecruitmentApiTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $response = $this->postJson('/api/v1/auth/login', ['email'=>'admin@ihrm.local','password'=>'password']);
        $this->token = $response->json('data.access_token');
    }

    public function test_auth_required(): void
    {
        $this->getJson('/api/v1/recruitment/requisitions')->assertStatus(401);
    }

    public function test_happy_path(): void
    {
        $departmentId = DepartmentModel::query()->value('id');
        $req = $this->withToken($this->token)->postJson('/api/v1/recruitment/requisitions', [
            'department_id' => $departmentId,
            'position' => 'Developer',
            'headcount' => 2,
            'reason' => 'Growth',
        ])->assertStatus(201);
        $reqId = $req->json('data.id');

        $this->withToken($this->token)->postJson("/api/v1/recruitment/requisitions/{$reqId}/submit")->assertStatus(200);

        $cand = $this->withToken($this->token)->postJson('/api/v1/recruitment/candidates', [
            'requisition_id' => $reqId,
            'full_name' => 'Alice Candidate',
            'email' => 'alice@example.com',
            'phone' => '0900000000',
            'source' => 'manual',
        ])->assertStatus(201);
        $candId = $cand->json('data.id');

        $int = $this->withToken($this->token)->postJson('/api/v1/recruitment/interviews', [
            'candidate_id' => $candId,
            'requisition_id' => $reqId,
            'interviewers' => ['interviewer-1'],
            'scheduled_at' => '2026-07-10 10:00:00',
        ])->assertStatus(201);
        $intId = $int->json('data.id');

        $this->withToken($this->token)->postJson("/api/v1/recruitment/interviews/{$intId}/scorecard", [
            'interviewer_id' => 'interviewer-1',
            'score' => 8,
            'comment' => 'Good',
        ])->assertStatus(200);

        $offer = $this->withToken($this->token)->postJson('/api/v1/recruitment/offers', [
            'candidate_id' => $candId,
            'requisition_id' => $reqId,
            'terms' => ['salary' => 5000],
            'created_by' => \Ramsey\Uuid\Uuid::uuid7()->toString(),
        ])->assertStatus(201);
        $offerId = $offer->json('data.id');

        $resp = $this->withToken($this->token)->postJson("/api/v1/recruitment/offers/{$offerId}/accept");
        if ($resp->status() === 500) fwrite(STDERR, "ACCEPT 500: " . json_encode($resp->json()) . "\n");
        $resp->assertStatus(200);
        // Convert is no longer needed—listener creates Employee automatically
        $this->withToken($this->token)->postJson("/api/v1/recruitment/offers/{$offerId}/convert")->assertStatus(422);
    }

    public function test_duplicate_candidate_returns_422(): void
    {
        $payload = ['full_name'=>'Bob','email'=>'bob@example.com','phone'=>'0911111111','source'=>'manual'];
        $this->withToken($this->token)->postJson('/api/v1/recruitment/candidates', $payload)->assertStatus(201);
        $this->withToken($this->token)->postJson('/api/v1/recruitment/candidates', $payload)->assertStatus(422);
    }
}
