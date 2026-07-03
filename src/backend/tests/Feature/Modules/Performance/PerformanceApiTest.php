<?php

namespace Tests\Feature\Modules\Performance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceApiTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $response = $this->postJson('/api/v1/auth/login', ['email' => 'admin@ihrm.local', 'password' => 'password']);
        $this->token = $response->json('data.access_token');
    }

    public function test_auth_required(): void
    {
        $this->getJson('/api/v1/performance/cycles')->assertStatus(401);
        $this->getJson('/api/v1/performance/reviews')->assertStatus(401);
    }

    public function test_cycle_review_goal_happy_path(): void
    {
        $cycle = $this->withToken($this->token)->postJson('/api/v1/performance/cycles', [
            'code' => 'FY26-Q1', 'name' => 'FY26 Q1',
            'start_date' => '2026-07-01', 'end_date' => '2026-09-30',
            'scoring_rules' => ['weights' => ['goals' => 0.6, 'competencies' => 0.4], 'max_score' => 5.0],
        ])->assertCreated();
        $cycleId = $cycle->json('id');

        $this->withToken($this->token)->postJson("/api/v1/performance/cycles/{$cycleId}/activate")->assertOk();

        $review = $this->withToken($this->token)->postJson('/api/v1/performance/reviews', [
            'cycle_id' => $cycleId, 'employee_id' => '00000000-0000-0000-0000-000000000001',
        ])->assertCreated();
        $reviewId = $review->json('id');

        $goal = $this->withToken($this->token)->postJson('/api/v1/performance/goals', [
            'cycle_id' => $cycleId, 'employee_id' => '00000000-0000-0000-0000-000000000001',
            'title' => 'Ship X', 'weight' => 1.0, 'target_value' => 'Launched',
        ])->assertCreated();
        $goalId = $goal->json('id');

        $this->withToken($this->token)->postJson("/api/v1/performance/reviews/{$reviewId}/self", ['assessment' => ['rating' => 4]])->assertOk();
        $this->withToken($this->token)->postJson("/api/v1/performance/reviews/{$reviewId}/manager", ['assessment' => ['rating' => 4]])->assertOk();
        $this->withToken($this->token)->postJson("/api/v1/performance/reviews/{$reviewId}/hr", ['assessment' => ['rating' => 4]])->assertOk();
        $this->withToken($this->token)->postJson("/api/v1/performance/reviews/{$reviewId}/finalize", ['final_score' => 4.2])->assertOk();
        $this->withToken($this->token)->postJson("/api/v1/performance/goals/{$goalId}/complete", ['actual_value' => 'Launched'])->assertOk();

        $show = $this->withToken($this->token)->getJson("/api/v1/performance/reviews/{$reviewId}")->assertOk();
        $this->assertSame('finalized', $show->json('status'));
    }

    public function test_review_rejects_out_of_order_stage(): void
    {
        $cycle = $this->withToken($this->token)->postJson('/api/v1/performance/cycles', [
            'code' => 'FY26-Q2', 'name' => 'FY26 Q2',
            'start_date' => '2026-10-01', 'end_date' => '2026-12-31',
            'scoring_rules' => [],
        ])->assertCreated();
        $cycleId = $cycle->json('id');

        $review = $this->withToken($this->token)->postJson('/api/v1/performance/reviews', [
            'cycle_id' => $cycleId, 'employee_id' => '00000000-0000-0000-0000-000000000002',
        ])->assertCreated();
        $reviewId = $review->json('id');

        // Skip self -> submit manager should fail
        $this->withToken($this->token)->postJson("/api/v1/performance/reviews/{$reviewId}/manager", ['assessment' => ['rating' => 3]])->assertStatus(422);
    }

    public function test_template_crud(): void
    {
        $t = $this->withToken($this->token)->postJson('/api/v1/performance/templates', [
            'code' => 'ENG-L4', 'name' => 'Engineer L4', 'rules' => [['name' => 'Code quality', 'weight' => 0.5]],
        ])->assertCreated();
        $id = $t->json('id');

        $this->withToken($this->token)->getJson("/api/v1/performance/templates/{$id}")->assertOk()->assertJsonPath('code', 'ENG-L4');
        $this->withToken($this->token)->putJson("/api/v1/performance/templates/{$id}", ['code' => 'ENG-L5', 'name' => 'Engineer L5', 'rules' => []])->assertOk();
        $this->withToken($this->token)->deleteJson("/api/v1/performance/templates/{$id}")->assertOk();
    }
}
