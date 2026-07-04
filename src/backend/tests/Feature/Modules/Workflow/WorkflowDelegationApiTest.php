<?php

namespace Tests\Feature\Modules\Workflow;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WorkflowDelegationApiTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@ihrm.local',
            'password' => 'password',
        ]);

        $this->token = $response->json('data.access_token');
    }

    public function test_requires_authentication(): void
    {
        $this->postJson('/api/v1/workflow-delegations', [])->assertStatus(401);
    }

    public function test_create_and_list_delegation(): void
    {
        $delegatorId = (string) Str::uuid();
        $delegateId = (string) Str::uuid();
        $now = CarbonImmutable::now();

        $response = $this->withToken($this->token)->postJson('/api/v1/workflow-delegations', [
            'delegator_id' => $delegatorId,
            'delegate_id' => $delegateId,
            'role_type' => 'hr_manager',
            'start_at' => $now->toIso8601String(),
            'end_at' => $now->addDays(30)->toIso8601String(),
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseCount('workflow_delegations', 1);

        $list = $this->withToken($this->token)->getJson('/api/v1/workflow-delegations');
        $list->assertStatus(200);
        $list->assertJsonCount(1, 'data');
    }

    public function test_delete_delegation(): void
    {
        $delegatorId = (string) Str::uuid();
        $delegateId = (string) Str::uuid();
        $now = CarbonImmutable::now();

        $create = $this->withToken($this->token)->postJson('/api/v1/workflow-delegations', [
            'delegator_id' => $delegatorId,
            'delegate_id' => $delegateId,
            'role_type' => 'manager',
            'start_at' => $now->toIso8601String(),
            'end_at' => $now->addDays(7)->toIso8601String(),
        ]);

        $id = $create->json('data.id');
        $this->withToken($this->token)->deleteJson("/api/v1/workflow-delegations/{$id}")->assertStatus(204);
        $this->assertDatabaseMissing('workflow_delegations', ['id' => $id, 'active' => true]);
    }
}
