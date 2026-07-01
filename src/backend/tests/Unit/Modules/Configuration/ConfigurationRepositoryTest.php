<?php

namespace Tests\Unit\Modules\Configuration;

use App\Modules\Configuration\Domain\Repositories\CodeGenerationRuleRepositoryInterface;
use App\Modules\Configuration\Domain\Repositories\HolidayCalendarRepositoryInterface;
use App\Modules\Configuration\Domain\Repositories\LookupRepositoryInterface;
use App\Modules\Configuration\Domain\Repositories\NotificationThresholdRepositoryInterface;
use App\Modules\Configuration\Domain\Repositories\SystemSettingRepositoryInterface;
use App\Modules\Configuration\Infrastructure\Persistence\Repositories\EloquentCodeGenerationRuleRepository;
use App\Modules\Configuration\Infrastructure\Persistence\Repositories\EloquentHolidayCalendarRepository;
use App\Modules\Configuration\Infrastructure\Persistence\Repositories\EloquentLookupRepository;
use App\Modules\Configuration\Infrastructure\Persistence\Repositories\EloquentNotificationThresholdRepository;
use App\Modules\Configuration\Infrastructure\Persistence\Repositories\EloquentSystemSettingRepository;
use Tests\TestCase;

class ConfigurationRepositoryTest extends TestCase
{
    public function test_lookup_repository_binding(): void
    {
        $repo = app(LookupRepositoryInterface::class);
        $this->assertInstanceOf(EloquentLookupRepository::class, $repo);
    }

    public function test_code_generation_rule_repository_binding(): void
    {
        $repo = app(CodeGenerationRuleRepositoryInterface::class);
        $this->assertInstanceOf(EloquentCodeGenerationRuleRepository::class, $repo);
    }

    public function test_system_setting_repository_binding(): void
    {
        $repo = app(SystemSettingRepositoryInterface::class);
        $this->assertInstanceOf(EloquentSystemSettingRepository::class, $repo);
    }

    public function test_holiday_calendar_repository_binding(): void
    {
        $repo = app(HolidayCalendarRepositoryInterface::class);
        $this->assertInstanceOf(EloquentHolidayCalendarRepository::class, $repo);
    }

    public function test_notification_threshold_repository_binding(): void
    {
        $repo = app(NotificationThresholdRepositoryInterface::class);
        $this->assertInstanceOf(EloquentNotificationThresholdRepository::class, $repo);
    }
}
