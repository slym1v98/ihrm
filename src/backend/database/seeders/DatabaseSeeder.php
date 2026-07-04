<?php

namespace Database\Seeders;

use App\Modules\Configuration\Infrastructure\Seeders\ConfigurationSeeder;
use App\Modules\Identity\Infrastructure\Seeders\AdminUserSeeder;
use App\Modules\Identity\Infrastructure\Seeders\DemoUserSeeder;
use App\Modules\Identity\Infrastructure\Seeders\PermissionSeeder;
use App\Modules\Identity\Infrastructure\Seeders\RoleSeeder;
use App\Modules\Employee\Infrastructure\Seeders\DemoEmployeeSeeder;
use App\Modules\Leave\Infrastructure\Seeders\LeaveTypeSeeder;
use App\Modules\Leave\Infrastructure\Seeders\DemoLeaveSeeder;
use App\Modules\Payroll\Infrastructure\Seeders\PayrollComponentSeeder;
use App\Modules\Payroll\Infrastructure\Seeders\DemoPayrollSeeder;
use App\Modules\Organization\Infrastructure\Seeders\OrgStructureSeeder;
use App\Modules\Notification\Infrastructure\Seeders\NotificationTemplateSeeder;
use App\Modules\Reporting\Infrastructure\Seeders\ReportingDefinitionSeeder;
use App\Modules\Onboarding\Infrastructure\Seeders\OnboardingPermissionSeeder;
use App\Modules\Offboarding\Infrastructure\Seeders\OffboardingPermissionSeeder;
use App\Modules\Performance\Infrastructure\Seeders\PerformancePermissionSeeder;
use App\Modules\Training\Infrastructure\Seeders\TrainingPermissionSeeder;
use App\Modules\Asset\Infrastructure\Seeders\AssetPermissionSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            AdminUserSeeder::class,
            ConfigurationSeeder::class,
            OrgStructureSeeder::class,
            LeaveTypeSeeder::class,
            PayrollComponentSeeder::class,
            NotificationTemplateSeeder::class,
            ReportingDefinitionSeeder::class,
            OnboardingPermissionSeeder::class,
            OffboardingPermissionSeeder::class,
            PerformancePermissionSeeder::class,
            TrainingPermissionSeeder::class,
            AssetPermissionSeeder::class,
            DemoUserSeeder::class,
            DemoEmployeeSeeder::class,
            DemoLeaveSeeder::class,
            DemoPayrollSeeder::class,
        ]);
    }
}
