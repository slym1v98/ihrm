<?php

namespace Tests\Feature\Modules\Workflow;

use App\Modules\Identity\Infrastructure\Persistence\Eloquent\UserModel;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WorkflowSlaEscalationTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $res = $this->postJson('/api/v1/auth/login', ['email' => 'admin@ihrm.local', 'password' => 'password']);
        $this->token = $res->json('data.access_token');
    }

    public function test_sla_deadline_set_on_first_step(): void
    {
        $userId = (string) UserModel::query()->value('id');
        $templateId = (string) Str::uuid();
        $stepId = (string) Str::uuid();

        \DB::table('workflow_templates')->insert([
            'id' => $templateId, 'code' => 'test-sla-deadline', 'name' => 'SLA Test',
            'description' => null, 'active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        \DB::table('workflow_template_steps')->insert([
            'id' => $stepId, 'workflow_template_id' => $templateId, 'step_order' => 1,
            'name' => 'SLA Step', 'assignee_type' => 'specific_user', 'assignee_id' => $userId,
            'condition' => null, 'resolver_type' => null, 'resolver_config' => '{}',
            'execution_type' => 'sequential', 'escalation_sla_hours' => 2,
            'escalation_target_type' => 'specific_user',
            'escalation_target_config' => json_encode(['user_id' => $userId]),
        ]);

        $req = $this->withToken($this->token)->postJson('/api/v1/workflow-requests', [
            'workflow_template_id' => $templateId, 'subject_type' => 'generic', 'subject_id' => (string) Str::uuid(),
        ]);
        $req->assertStatus(200);
        $this->assertNotNull($req->json('data.sla_deadline_at'));
        $this->assertFalse($req->json('data.escalated'));
    }

    public function test_sla_escalate_command_flags_overdue(): void
    {
        $userId = (string) UserModel::query()->value('id');
        $templateId = (string) Str::uuid();
        $stepId = (string) Str::uuid();
        $reqId = (string) Str::uuid();

        \DB::table('workflow_templates')->insert([
            'id' => $templateId, 'code' => 'test-sla-cmd', 'name' => 'SLA Cmd',
            'description' => null, 'active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        \DB::table('workflow_template_steps')->insert([
            'id' => $stepId, 'workflow_template_id' => $templateId, 'step_order' => 1,
            'name' => 'SLA Step', 'assignee_type' => 'specific_user', 'assignee_id' => $userId,
            'condition' => null, 'resolver_type' => null, 'resolver_config' => '{}',
            'execution_type' => 'sequential', 'escalation_sla_hours' => 1,
            'escalation_target_type' => null, 'escalation_target_config' => null,
        ]);
        \DB::table('workflow_requests')->insert([
            'id' => $reqId, 'workflow_template_id' => $templateId,
            'subject_type' => 'generic', 'subject_id' => (string) Str::uuid(),
            'submitted_by' => $userId, 'status' => 'in_review', 'current_step' => 1,
            'context' => '{}', 'sla_deadline_at' => now()->subHour(),
            'escalated' => false, 'parallel_approved_count' => 0, 'parallel_required_count' => 0,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->artisan('workflow:sla-escalate')->assertExitCode(0);
        $this->assertDatabaseHas('workflow_requests', ['id' => $reqId, 'escalated' => true]);
    }
}
