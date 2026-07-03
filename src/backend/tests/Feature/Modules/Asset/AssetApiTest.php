<?php
namespace Tests\Feature\Modules\Asset;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetApiTest extends TestCase
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
        $this->getJson('/api/v1/assets/items')->assertStatus(401);
        $this->postJson('/api/v1/assets/assignments', [])->assertStatus(401);
    }

    public function test_create_and_read_asset_item(): void
    {
        $res = $this->withToken($this->token)->postJson('/api/v1/assets/items', [
            'asset_code' => 'LAP-001',
            'asset_type' => 'laptop',
            'name' => 'MacBook Pro 16',
            'serial_number' => 'SN12345',
            'condition' => 'new',
        ]);
        $res->assertStatus(201);
        $this->assertDatabaseHas('asset_items', ['asset_code' => 'LAP-001']);

        $this->withToken($this->token)
            ->getJson('/api/v1/assets/items')
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_assign_asset_and_obligations(): void
    {
        // Create item
        $create = $this->withToken($this->token)->postJson('/api/v1/assets/items', [
            'asset_code' => 'PHN-001',
            'asset_type' => 'phone',
            'name' => 'iPhone 15',
            'condition' => 'new',
        ])->assertStatus(201);
        $itemId = $create->json('data.id');

        // Assign to employee
        $assign = $this->withToken($this->token)->postJson('/api/v1/assets/assignments', [
            'asset_item_id' => $itemId,
            'employee_id' => '00000000-0000-0000-0000-000000000001',
        ])->assertStatus(201);

        // Item now assigned
        $this->assertDatabaseHas('asset_items', ['id' => $itemId, 'status' => 'assigned']);

        // Obligations listed
        $this->withToken($this->token)
            ->getJson('/api/v1/assets/employees/00000000-0000-0000-0000-000000000001/obligations')
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_cannot_assign_already_assigned_asset(): void
    {
        $create = $this->withToken($this->token)->postJson('/api/v1/assets/items', [
            'asset_code' => 'MON-001',
            'asset_type' => 'monitor',
            'name' => 'Dell 27"',
            'condition' => 'good',
        ])->assertStatus(201);
        $itemId = $create->json('data.id');

        $this->withToken($this->token)->postJson('/api/v1/assets/assignments', [
            'asset_item_id' => $itemId,
            'employee_id' => '00000000-0000-0000-0000-000000000001',
        ])->assertStatus(201);

        $this->withToken($this->token)->postJson('/api/v1/assets/assignments', [
            'asset_item_id' => $itemId,
            'employee_id' => '00000000-0000-0000-0000-000000000002',
        ])->assertStatus(422);
    }

    public function test_return_asset(): void
    {
        $create = $this->withToken($this->token)->postJson('/api/v1/assets/items', [
            'asset_code' => 'KB-001',
            'asset_type' => 'keyboard',
            'name' => 'Logitech MX',
            'condition' => 'new',
        ])->assertStatus(201);
        $itemId = $create->json('data.id');

        $assign = $this->withToken($this->token)->postJson('/api/v1/assets/assignments', [
            'asset_item_id' => $itemId,
            'employee_id' => '00000000-0000-0000-0000-000000000001',
        ])->assertStatus(201);
        $assignmentId = $assign->json('data.id');

        $this->withToken($this->token)->postJson("/api/v1/assets/assignments/{$assignmentId}/return", [
            'condition_on_return' => 'good',
        ])->assertStatus(201);

        $this->assertDatabaseHas('asset_items', ['id' => $itemId, 'status' => 'available']);
        $this->assertDatabaseHas('asset_assignments', ['id' => $assignmentId, 'status' => 'returned']);
    }

    public function test_status_action_mark_maintenance(): void
    {
        $create = $this->withToken($this->token)->postJson('/api/v1/assets/items', [
            'asset_code' => 'DESK-001',
            'asset_type' => 'furniture',
            'name' => 'Standing Desk',
            'condition' => 'fair',
        ])->assertStatus(201);
        $itemId = $create->json('data.id');

        $this->withToken($this->token)
            ->postJson("/api/v1/assets/items/{$itemId}/mark-maintenance")
            ->assertStatus(200);

        $this->assertDatabaseHas('asset_items', ['id' => $itemId, 'status' => 'maintenance']);
    }

    public function test_delete_item_with_history_fails(): void
    {
        $create = $this->withToken($this->token)->postJson('/api/v1/assets/items', [
            'asset_code' => 'CHAIR-001',
            'asset_type' => 'furniture',
            'name' => 'Office Chair',
            'condition' => 'new',
        ])->assertStatus(201);
        $itemId = $create->json('data.id');

        $this->withToken($this->token)->postJson('/api/v1/assets/assignments', [
            'asset_item_id' => $itemId,
            'employee_id' => '00000000-0000-0000-0000-000000000001',
        ])->assertStatus(201);

        $this->withToken($this->token)
            ->deleteJson("/api/v1/assets/items/{$itemId}")
            ->assertStatus(422);
    }
}
