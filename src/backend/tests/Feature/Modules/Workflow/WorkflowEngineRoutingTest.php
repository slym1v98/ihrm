<?php

namespace Tests\Feature\Modules\Workflow;

use App\Modules\Identity\Infrastructure\Persistence\Eloquent\UserModel;
use App\Modules\Leave\Infrastructure\Persistence\Eloquent\LeaveTypeModel;
use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WorkflowEngineRoutingTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@ihrm.local',
            'password' => 'password',
        ]);

        $this->token = $response->json('data.access_token');
    }

    public function test_engine_routes_skip_step_with_false_condition(): void
    {
        $templateId = (string) Str::uuid();
        $step1Id = (string) Str::uuid();
        $step2Id = (string) Str::uuid();
        $userId = (string) UserModel::query()->value('id');

        \DB::table('workflow_templates')->insert([
            'id' => $templateId,
            'code' => 'test-skip',
            'name' => 'Test Skip',
            'description' => null,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('workflow_template_steps')->insert([
            ['id' => $step1Id, 'workflow_template_id' => $templateId, 'step_order' => 1, 'name' => 'Step 1', 'assignee_type' => 'specific_user', 'assignee_id' => $userId, 'condition' => null, 'resolver_type' => null, 'resolver_config' => '{}'],
            ['id' => $step2Id, 'workflow_template_id' => $templateId, 'step_order' => 2, 'name' => 'Step 2 (skipped)', 'assignee_type' => 'specific_user', 'assignee_id' => (string) Str::uuid(), 'condition' => json_encode(['field' => 'value', 'op' => 'eq', 'value' => 'nope']), 'resolver_type' => null, 'resolver_config' => '{}'],
            ['id' => (string) Str::uuid(), 'workflow_template_id' => $templateId, 'step_order' => 3, 'name' => 'Step 3', 'assignee_type' => 'specific_user', 'assignee_id' => $userId, 'condition' => null, 'resolver_type' => null, 'resolver_config' => '{}'],
        ]);

        $response = $this->withToken($this->token)->postJson('/api/v1/workflow-requests', [
            'workflow_template_id' => $templateId,
            'subject_type' => 'generic',
            'subject_id' => (string) Str::uuid(),
        ]);

        $reqId = $response->json('data.id');
        $this->assertEquals('in_review', $response->json('data.status'));
        $this->assertEquals(1, $response->json('data.current_step'));

        // Approve step 1 → step 2 (condition:false) skipped → step 3 becomes active
        $approve = $this->withToken($this->token)->postJson("/api/v1/workflow-requests/{$reqId}/approve", [
            'comment' => 'Step 1 approved',
        ]);
        $status = $approve->status();
        if ($status !== 204) {
            echo "\nAPPROVE STATUS: {$status} BODY: ".json_encode($approve->json())."\n";
        }
        if ($status === 500) {
            fwrite(STDERR, '500 BODY: '.json_encode($approve->json())."\n");
        }
        $approve->assertStatus(204);
        $this->assertDatabaseHas('workflow_requests', ['id' => $reqId, 'current_step' => 3, 'status' => 'in_review']);

        // Approve step 3 → done
        $final = $this->withToken($this->token)->postJson("/api/v1/workflow-requests/{$reqId}/approve", [
            'comment' => 'Step 3 approved',
        ]);
        $final->assertStatus(204);
        $this->assertDatabaseHas('workflow_requests', ['id' => $reqId, 'status' => 'approved']);
    }

    public function test_leave_create_workflow_and_approve_through_workflow(): void
    {
        $user = UserModel::query()->first();
        $userId = (string) $user->id;
        $type = LeaveTypeModel::query()->where('is_balance_tracked', false)->first();
        $this->assertNotNull($type, 'non-tracked leave type must exist after seed');
        $this->assertFalse($type->is_balance_tracked);
        LeaveTypeModel::query()
            ->where('id', $type->id)->update(['workflow_template_code' => 'leave-approval']);

        $templateId = (string) Str::uuid();
        $stepId = (string) Str::uuid();
        \DB::table('workflow_templates')->insert([
            'id' => $templateId,
            'code' => 'leave-approval',
            'name' => 'Leave Approval',
            'description' => null,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        \DB::table('workflow_template_steps')->insert([
            'id' => $stepId,
            'workflow_template_id' => $templateId,
            'step_order' => 1,
            'name' => 'Manager Approve',
            'assignee_type' => 'specific_user',
            'assignee_id' => $userId,
            'condition' => null,
            'resolver_type' => null,
            'resolver_config' => '{}',
        ]);

        $response = $this->withToken($this->token)->postJson('/api/v1/leave-requests', [
            'leave_type_id' => $type->id,
            'start_at' => CarbonImmutable::now()->addDays(10)->toDateString(),
            'end_at' => CarbonImmutable::now()->addDays(10)->toDateString(),
            'duration_unit' => 'day',
            'reason' => 'Workflow test',
        ]);
        $response->assertStatus(200);
        $leaveId = $response->json('data.id');
        $this->assertEquals('pending', $response->json('data.status'));

        $this->assertDatabaseHas('workflow_requests', [
            'subject_type' => 'leave_request',
            'subject_id' => $leaveId,
        ]);

        $wfReq = \DB::table('workflow_requests')->where('subject_type', 'leave_request')->where('subject_id', $leaveId)->first();
        $this->assertNotNull($wfReq);

        $approve = $this->withToken($this->token)->postJson("/api/v1/workflow-requests/{$wfReq->id}/approve", [
            'comment' => 'Approved',
        ]);
        $approve->assertStatus(204);
        $this->assertDatabaseHas('workflow_requests', ['id' => $wfReq->id, 'status' => 'approved']);

        // Leave should be approved via listener
        $this->assertDatabaseHas('leave_requests', ['id' => $leaveId, 'status' => 'approved']);
    }
}
