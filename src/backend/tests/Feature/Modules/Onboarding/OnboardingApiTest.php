<?php

namespace Tests\Feature\Modules\Onboarding;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingApiTest extends TestCase
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
        $this->getJson('/api/v1/onboarding/templates')->assertStatus(401);
        $this->postJson('/api/v1/onboarding/templates', [])->assertStatus(401);
        $this->getJson('/api/v1/onboarding/plans')->assertStatus(401);
        $this->postJson('/api/v1/onboarding/plans', [])->assertStatus(401);
    }

    public function test_template_crud(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/v1/onboarding/templates', [
            'code' => 'default-onboarding',
            'name' => 'Default Onboarding',
            'rules' => [
                'tasks' => [
                    [
                        'title' => 'Prepare laptop',
                        'owner_type' => 'department',
                        'owner_id' => 'it',
                        'due_days' => -7,
                        'requires_approval' => true,
                        'is_pre_start' => true,
                        'sort_order' => 1,
                    ],
                ],
            ],
        ]);
        $response->assertCreated();
        $templateId = $response->json('data.id');
        $this->assertNotNull($templateId);

        $this->withToken($this->token)->getJson('/api/v1/onboarding/templates')->assertOk();
        $this->withToken($this->token)->getJson("/api/v1/onboarding/templates/{$templateId}")->assertOk();
        $this->withToken($this->token)->deleteJson("/api/v1/onboarding/templates/{$templateId}")->assertNoContent();
    }

    public function test_plan_happy_path(): void
    {
        $templateResponse = $this->withToken($this->token)->postJson('/api/v1/onboarding/templates', [
            'code' => 'plan-test',
            'name' => 'Plan Test',
            'rules' => [
                'tasks' => [
                    [
                        'title' => 'Mandatory task',
                        'owner_type' => 'user_role',
                        'owner_id' => 'hr',
                        'due_days' => 0,
                        'requires_approval' => false,
                        'is_pre_start' => false,
                        'sort_order' => 1,
                    ],
                ],
            ],
        ]);
        $templateId = $templateResponse->json('data.id');

        $planResponse = $this->withToken($this->token)->postJson('/api/v1/onboarding/plans', [
            'employee_id' => '00000000-0000-0000-0000-000000000001',
            'template_id' => $templateId,
            'start_date' => '2026-07-15',
        ]);
        $planResponse->assertCreated();
        $planId = $planResponse->json('data.id');

        $this->assertEquals('draft', $planResponse->json('data.status'));
        $this->withToken($this->token)->postJson("/api/v1/onboarding/plans/{$planId}/activate")->assertOk();

        $tasksResponse = $this->withToken($this->token)->getJson("/api/v1/onboarding/plans/{$planId}/tasks");
        $tasksResponse->assertOk();
        $taskId = $tasksResponse->json('data.0.id');
        $this->assertNotNull($taskId);

        $this->withToken($this->token)->postJson("/api/v1/onboarding/tasks/{$taskId}/start")->assertOk();
        $this->withToken($this->token)->postJson("/api/v1/onboarding/tasks/{$taskId}/complete")->assertOk();
        $this->withToken($this->token)->postJson("/api/v1/onboarding/plans/{$planId}/complete")->assertOk();
    }

    public function test_add_custom_task(): void
    {
        $planResponse = $this->withToken($this->token)->postJson('/api/v1/onboarding/plans', [
            'employee_id' => '00000000-0000-0000-0000-000000000001',
            'start_date' => '2026-07-15',
        ]);
        $planId = $planResponse->json('data.id');

        $this->withToken($this->token)->postJson("/api/v1/onboarding/plans/{$planId}/tasks", [
            'title' => 'Welcome email',
            'owner_type' => 'user_role',
            'owner_id' => 'hr',
        ])->assertCreated();
    }

    public function test_plan_complete_fails_with_pending_task(): void
    {
        $templateResponse = $this->withToken($this->token)->postJson('/api/v1/onboarding/templates', [
            'code' => 'pending-test',
            'name' => 'Pending Test',
            'rules' => [
                'tasks' => [
                    [
                        'title' => 'Pending task',
                        'owner_type' => 'user_role',
                        'owner_id' => 'hr',
                        'due_days' => 0,
                        'requires_approval' => false,
                        'is_pre_start' => false,
                        'sort_order' => 1,
                    ],
                ],
            ],
        ]);

        $planResponse = $this->withToken($this->token)->postJson('/api/v1/onboarding/plans', [
            'employee_id' => '00000000-0000-0000-0000-000000000001',
            'template_id' => $templateResponse->json('data.id'),
            'start_date' => '2026-07-15',
        ]);
        $planId = $planResponse->json('data.id');

        $this->withToken($this->token)->postJson("/api/v1/onboarding/plans/{$planId}/activate")->assertOk();
        $this->withToken($this->token)->postJson("/api/v1/onboarding/plans/{$planId}/complete")->assertStatus(422);
    }
}
