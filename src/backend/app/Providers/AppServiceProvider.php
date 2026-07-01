<?php

namespace App\Providers;

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
use App\Modules\Identity\Domain\Repositories\RoleRepositoryInterface;
use App\Modules\Identity\Domain\Repositories\UserRepositoryInterface;
use App\Modules\Identity\Infrastructure\Persistence\Repositories\EloquentRoleRepository;
use App\Modules\Identity\Infrastructure\Persistence\Repositories\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, EloquentRoleRepository::class);
        $this->app->bind(LookupRepositoryInterface::class, EloquentLookupRepository::class);
        $this->app->bind(CodeGenerationRuleRepositoryInterface::class, EloquentCodeGenerationRuleRepository::class);
        $this->app->bind(SystemSettingRepositoryInterface::class, EloquentSystemSettingRepository::class);
        $this->app->bind(HolidayCalendarRepositoryInterface::class, EloquentHolidayCalendarRepository::class);
        $this->app->bind(NotificationThresholdRepositoryInterface::class, EloquentNotificationThresholdRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
