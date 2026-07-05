<?php

namespace Tests\Feature\Modules\Configuration;

use App\Modules\Configuration\Infrastructure\Persistence\Eloquent\CodeGenerationRuleModel;
use App\Modules\Configuration\Infrastructure\Persistence\Eloquent\HolidayCalendarModel;
use App\Modules\Configuration\Infrastructure\Persistence\Eloquent\NotificationThresholdModel;
use App\Modules\Configuration\Infrastructure\Persistence\Eloquent\SystemSettingModel;
use App\Modules\Identity\Infrastructure\Persistence\Eloquent\UserModel;
use App\Modules\Identity\Infrastructure\Seeders\AdminUserSeeder;
use App\Modules\Identity\Infrastructure\Seeders\PermissionSeeder;
use App\Modules\Identity\Infrastructure\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfigurationHttpTest extends TestCase
{
    use RefreshDatabase;

    private function seedAdmin(): UserModel
    {
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(AdminUserSeeder::class);

        return UserModel::where('email', 'admin@ihrm.local')->firstOrFail();
    }

    // === Code Generation Rules ===

    public function test_list_code_generation_rules(): void
    {
        $admin = $this->seedAdmin();
        CodeGenerationRuleModel::create(['entity_type' => 'employee', 'prefix' => 'EMP', 'pattern' => '{prefix}-{yyyy}-{seq}', 'next_number' => 1, 'sequence_padding' => 5, 'active' => true]);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/config/code-generation-rules')
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'entity_type', 'prefix', 'pattern']]]);
    }

    public function test_preview_code(): void
    {
        $admin = $this->seedAdmin();
        CodeGenerationRuleModel::create(['entity_type' => 'employee', 'prefix' => 'EMP', 'pattern' => '{prefix}-{yyyy}-{seq}', 'next_number' => 7, 'sequence_padding' => 4, 'active' => true]);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/config/code-generation-rules/employee/preview')
            ->assertOk()
            ->assertJsonPath('data.code', 'EMP-'.now()->format('Y').'-0007');
    }

    public function test_next_code_increments(): void
    {
        $admin = $this->seedAdmin();
        $rule = CodeGenerationRuleModel::create(['entity_type' => 'contract', 'prefix' => 'CTR', 'pattern' => '{prefix}-{seq}', 'next_number' => 10, 'sequence_padding' => 3, 'active' => true]);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/config/code-generation-rules/contract/next')
            ->assertOk()
            ->assertJsonPath('data.code', 'CTR-010');

        $this->assertSame(11, $rule->fresh()->next_number);
    }

    // === System Settings ===

    public function test_list_settings(): void
    {
        $admin = $this->seedAdmin();
        SystemSettingModel::create(['key' => 'company.name', 'value' => 'iHRM', 'value_type' => 'string', 'editable' => true]);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/config/settings')
            ->assertOk()
            ->assertJsonPath('data.0.key', 'company.name');
    }

    public function test_update_editable_setting(): void
    {
        $admin = $this->seedAdmin();
        $setting = SystemSettingModel::create(['key' => 'locale.timezone', 'value' => 'UTC', 'value_type' => 'string', 'editable' => true]);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/config/settings', ['key' => 'locale.timezone', 'value' => 'Asia/Ho_Chi_Minh', 'value_type' => 'string'])
            ->assertCreated()
            ->assertJsonPath('data.value', 'Asia/Ho_Chi_Minh');
    }

    public function test_cannot_update_non_editable_setting(): void
    {
        $admin = $this->seedAdmin();
        SystemSettingModel::create(['key' => 'system.version', 'value' => '1.0.0', 'value_type' => 'string', 'editable' => false]);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/config/settings', ['key' => 'system.version', 'value' => '2.0.0', 'value_type' => 'string'])
            ->assertStatus(422)
            ->assertJsonStructure(['error']);
    }

    // === Holiday Calendars ===

    public function test_list_holiday_calendars(): void
    {
        $admin = $this->seedAdmin();
        HolidayCalendarModel::create(['code' => 'vn-2026', 'name' => 'Vietnam 2026', 'year' => 2026, 'active' => true]);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/config/holiday-calendars')
            ->assertOk()
            ->assertJsonPath('data.0.code', 'vn-2026');
    }

    public function test_create_holiday_calendar(): void
    {
        $admin = $this->seedAdmin();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/config/holiday-calendars', ['code' => 'vn-2026', 'name' => 'Vietnam 2026', 'year' => 2026, 'active' => true])
            ->assertCreated()
            ->assertJsonPath('data.code', 'vn-2026');
    }

    public function test_add_holiday_to_calendar(): void
    {
        $admin = $this->seedAdmin();
        $calendar = HolidayCalendarModel::create(['code' => 'vn-2026', 'name' => 'Vietnam 2026', 'year' => 2026, 'active' => true]);

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/config/holiday-calendars/{$calendar->id}/holidays", ['date' => '2026-01-01', 'name' => 'New Year', 'paid' => true])
            ->assertCreated()
            ->assertJsonPath('data.name', 'New Year');
    }

    // === Notification Thresholds ===

    public function test_list_thresholds(): void
    {
        $admin = $this->seedAdmin();
        NotificationThresholdModel::create(['code' => 'contract.expiry', 'target_type' => 'contract', 'days_before' => 30, 'active' => true]);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/config/notification-thresholds')
            ->assertOk()
            ->assertJsonPath('data.0.code', 'contract.expiry');
    }

    public function test_create_threshold(): void
    {
        $admin = $this->seedAdmin();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/config/notification-thresholds', ['code' => 'document.expiry', 'target_type' => 'document', 'days_before' => 14, 'active' => true])
            ->assertCreated()
            ->assertJsonPath('data.code', 'document.expiry');
    }
}
