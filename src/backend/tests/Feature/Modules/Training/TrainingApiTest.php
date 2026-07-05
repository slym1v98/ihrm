<?php

namespace Tests\Feature\Modules\Training;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingApiTest extends TestCase
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
        $this->getJson('/api/v1/training/courses')->assertStatus(401);
        $this->postJson('/api/v1/training/courses', [])->assertStatus(401);
    }

    public function test_happy_path(): void
    {
        // Create course
        $course = $this->withToken($this->token)->postJson('/api/v1/training/courses', [
            'code' => 'ONBOARD-101', 'name' => 'New Hire Onboarding',
            'description' => 'Basic onboarding course', 'category' => 'onboarding',
            'default_duration_hours' => 8, 'max_participants' => 30,
        ])->assertCreated();
        $courseId = $course->json('data.id');
        $this->assertNotNull($courseId);

        // Create session
        $session = $this->withToken($this->token)->postJson("/api/v1/training/courses/{$courseId}/sessions", [
            'code' => 'ONBOARD-101-JUL', 'name' => 'July session',
            'start_date' => '2026-07-15 09:00:00', 'end_date' => '2026-07-15 17:00:00',
            'location' => 'Room A', 'instructor' => 'Trainer A', 'max_participants' => 2,
        ])->assertCreated();
        $sessionId = $session->json('id');

        // Enroll
        $enroll = $this->withToken($this->token)->postJson("/api/v1/training/sessions/{$sessionId}/enroll", [
            'employee_id' => '00000000-0000-0000-0000-000000000001',
        ])->assertCreated();
        $enrollmentId = $enroll->json('id');

        // Record attendance
        $this->withToken($this->token)->postJson("/api/v1/training/enrollments/{$enrollmentId}/attendance", [
            'attendance' => ['present' => true, 'checked_in_at' => '2026-07-15 09:00:00'],
        ])->assertOk();

        // Complete enrollment
        $this->withToken($this->token)->postJson("/api/v1/training/enrollments/{$enrollmentId}/complete")->assertOk();

        // Record result
        $result = $this->withToken($this->token)->postJson("/api/v1/training/enrollments/{$enrollmentId}/result", [
            'score' => 85.5, 'passed' => true, 'certificate_code' => 'CERT-001',
        ])->assertCreated();
        $resultId = $result->json('id');

        // Fetch result
        $this->withToken($this->token)->getJson("/api/v1/training/results/{$resultId}")->assertOk()->assertJsonPath('certificate_code', 'CERT-001');
    }

    public function test_capacity_guard(): void
    {
        $course = $this->withToken($this->token)->postJson('/api/v1/training/courses', [
            'code' => 'CAPACITY-TEST', 'name' => 'Capacity Test',
            'max_participants' => 1,
        ])->assertCreated();
        $courseId = $course->json('data.id');

        $session = $this->withToken($this->token)->postJson("/api/v1/training/courses/{$courseId}/sessions", [
            'code' => 'CAP-1', 'name' => 'Session 1',
            'start_date' => '2026-08-01 09:00:00', 'end_date' => '2026-08-01 17:00:00',
            'max_participants' => 1,
        ])->assertCreated();
        $sessionId = $session->json('id');

        // First enroll succeeds
        $this->withToken($this->token)->postJson("/api/v1/training/sessions/{$sessionId}/enroll", [
            'employee_id' => '00000000-0000-0000-0000-000000000001',
        ])->assertCreated();

        // Second enroll fails
        $this->withToken($this->token)->postJson("/api/v1/training/sessions/{$sessionId}/enroll", [
            'employee_id' => '00000000-0000-0000-0000-000000000002',
        ])->assertStatus(422);
    }

    public function test_duplicate_enrollment_guard(): void
    {
        $course = $this->withToken($this->token)->postJson('/api/v1/training/courses', [
            'code' => 'DUP-TEST', 'name' => 'Duplicate Test',
        ])->assertCreated();
        $courseId = $course->json('data.id');

        $session = $this->withToken($this->token)->postJson("/api/v1/training/courses/{$courseId}/sessions", [
            'code' => 'DUP-1', 'name' => 'Session 1',
            'start_date' => '2026-08-01 09:00:00', 'end_date' => '2026-08-01 17:00:00',
            'max_participants' => 10,
        ])->assertCreated();
        $sessionId = $session->json('id');

        $this->withToken($this->token)->postJson("/api/v1/training/sessions/{$sessionId}/enroll", [
            'employee_id' => '00000000-0000-0000-0000-000000000001',
        ])->assertCreated();

        $this->withToken($this->token)->postJson("/api/v1/training/sessions/{$sessionId}/enroll", [
            'employee_id' => '00000000-0000-0000-0000-000000000001',
        ])->assertStatus(422);
    }
}
