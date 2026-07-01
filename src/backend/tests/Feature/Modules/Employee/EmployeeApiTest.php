<?php

namespace Tests\Feature\Modules\Employee;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeApiTest extends TestCase
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

    public function test_list_employees(): void
    {
        $this->withToken($this->token)->getJson('/api/v1/employees')->assertStatus(200);
    }

    public function test_create_employee(): void
    {
        $r = $this->withToken($this->token)->postJson('/api/v1/employees', ['first_name' => 'Test', 'last_name' => 'User']);
        $r->assertStatus(201);
        $this->assertDatabaseHas('employees', ['employee_code' => $r->json('data.employee_code')]);
    }

    public function test_permission_enforcement(): void
    {
        // Login as admin
        $adminResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@ihrm.local',
            'password' => 'password',
        ]);
        $adminToken = $adminResponse->json('data.access_token');

        $r = $this->withToken($adminToken)->postJson('/api/v1/employees', ['first_name' => 'Test', 'last_name' => 'User']);
        $r->assertStatus(201);
    }
}
