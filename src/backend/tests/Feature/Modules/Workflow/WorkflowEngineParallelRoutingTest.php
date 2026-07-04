<?php

namespace Tests\Feature\Modules\Workflow;

use App\Modules\Identity\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WorkflowEngineParallelRoutingTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $res = $this->postJson('/api/v1/auth/login', ['email' => 'admin@ihrm.local', 'password' => 'password']);
        $this->token = $res->json('data.access_token');
    }

    private function insertStep(string $templateId, int $order, string $type, ?string $userId = null): string
    {
        $u = $userId ?? (string) UserModel::query()->value('id');
        $id = (string) Str::uuid();
        \DB::table('workflow_template_steps')->insert([
            'id' => $id, 'workflow_template_id' => $templateId, 'step_order' => $order,
            'name' => "Step {$order}", 'assignee_type' => 'specific_user', 'assignee_id' => $u,
            'condition' => null, 'resolver_type' => null, 'resolver_config' => '{}',
            'execution_type' => $type, 'escalation_sla_hours' => null,
            'escalation_target_type' => null, 'escalation_target_config' => null,
        ]);
        return $id;
    }

    private function insertTemplate(string $code): string
    {
        $id = (string) Str::uuid();
        \DB::table('workflow_templates')->insert([
            'id' => $id, 'code' => $code, 'name' => $code, 'description' => null,
            'active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        return $id;
    }

    public function test_any_of_approves_after_first_approver(): void
    {
        $templateId = $this->insertTemplate('test-any-of-parallel');
        $this->insertStep($templateId, 1, 'any_of');

        $req = $this->withToken($this->token)->postJson('/api/v1/workflow-requests', [
            'workflow_template_id' => $templateId, 'subject_type' => 'generic', 'subject_id' => (string) Str::uuid(),
        ]);
        $req->assertStatus(200);
        $reqId = $req->json('data.id');

        $approve = $this->withToken($this->token)->postJson("/api/v1/workflow-requests/{$reqId}/approve", ['comment' => 'go']);
        $approve->assertStatus(204);
        $this->assertDatabaseHas('workflow_requests', ['id' => $reqId, 'status' => 'approved']);
    }

    public function test_all_of_stays_pending_until_all_approve(): void
    {
        $templateId = $this->insertTemplate('test-all-of-parallel');
        $userId = (string) UserModel::query()->value('id');
        $this->insertStep($templateId, 1, 'all_of', $userId);

        $req = $this->withToken($this->token)->postJson('/api/v1/workflow-requests', [
            'workflow_template_id' => $templateId, 'subject_type' => 'generic', 'subject_id' => (string) Str::uuid(),
        ]);
        $req->assertStatus(200);
        $reqId = $req->json('data.id');
        $this->assertEquals(1, $req->json('data.parallel_required_count'));

        $approve = $this->withToken($this->token)->postJson("/api/v1/workflow-requests/{$reqId}/approve", ['comment' => 'go']);
        $approve->assertStatus(204);
        // parallel_required_count = 1 (1 resolver result) → one approve needed → approved
        $this->assertDatabaseHas('workflow_requests', ['id' => $reqId, 'status' => 'approved']);
    }
}
