<?php

namespace Database\Seeders;

use App\Modules\Asset\Infrastructure\Seeders\AssetPermissionSeeder;
use App\Modules\Asset\Infrastructure\Seeders\DemoAssetSeeder;
use App\Modules\Attendance\Infrastructure\Seeders\AttendancePeriodSeeder;
use App\Modules\Attendance\Infrastructure\Seeders\DemoAttendanceSeeder;
use App\Modules\Audit\Infrastructure\Seeders\DemoAuditSeeder;
use App\Modules\Configuration\Infrastructure\Seeders\ConfigurationSeeder;
use App\Modules\Employee\Infrastructure\Seeders\DemoEmployeeSeeder;
use App\Modules\Identity\Infrastructure\Seeders\AdminUserSeeder;
use App\Modules\Identity\Infrastructure\Seeders\DemoUserSeeder;
use App\Modules\Identity\Infrastructure\Seeders\PermissionSeeder;
use App\Modules\Identity\Infrastructure\Seeders\RoleSeeder;
use App\Modules\Leave\Infrastructure\Seeders\DemoLeaveSeeder;
use App\Modules\Leave\Infrastructure\Seeders\LeaveTypeSeeder;
use App\Modules\Notification\Infrastructure\Seeders\DemoNotificationSeeder;
use App\Modules\Notification\Infrastructure\Seeders\NotificationTemplateSeeder;
use App\Modules\Offboarding\Infrastructure\Seeders\DemoOffboardingSeeder;
use App\Modules\Offboarding\Infrastructure\Seeders\OffboardingPermissionSeeder;
use App\Modules\Onboarding\Infrastructure\Seeders\DemoOnboardingSeeder;
use App\Modules\Onboarding\Infrastructure\Seeders\OnboardingPermissionSeeder;
use App\Modules\Organization\Infrastructure\Seeders\OrgStructureSeeder;
use App\Modules\Payroll\Infrastructure\Seeders\DemoPayrollSeeder;
use App\Modules\Payroll\Infrastructure\Seeders\PayrollComponentSeeder;
use App\Modules\Performance\Infrastructure\Seeders\DemoPerformanceSeeder;
use App\Modules\Performance\Infrastructure\Seeders\PerformancePermissionSeeder;
use App\Modules\Recruitment\Infrastructure\Seeders\DemoRecruitmentSeeder;
use App\Modules\Reporting\Infrastructure\Seeders\DemoReportingSeeder;
use App\Modules\Reporting\Infrastructure\Seeders\ReportingDefinitionSeeder;
use App\Modules\Shift\Infrastructure\Seeders\DemoShiftSeeder;
use App\Modules\Shift\Infrastructure\Seeders\ShiftTemplateSeeder;
use App\Modules\Training\Infrastructure\Seeders\DemoTrainingSeeder;
use App\Modules\Training\Infrastructure\Seeders\TrainingPermissionSeeder;
use App\Modules\Workflow\Infrastructure\Seeders\DemoWorkflowSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Infrastructure
            PermissionSeeder::class,
            RoleSeeder::class,
            AdminUserSeeder::class,
            ConfigurationSeeder::class,

            // Organization (needed by Employee)
            OrgStructureSeeder::class,

            // Master data
            ShiftTemplateSeeder::class,
            AttendancePeriodSeeder::class,
            LeaveTypeSeeder::class,
            PayrollComponentSeeder::class,
            NotificationTemplateSeeder::class,
            ReportingDefinitionSeeder::class,
            DemoPerformanceSeeder::class,
            DemoTrainingSeeder::class,

            // Permission-only
            OnboardingPermissionSeeder::class,
            OffboardingPermissionSeeder::class,
            PerformancePermissionSeeder::class,
            TrainingPermissionSeeder::class,
            AssetPermissionSeeder::class,

            // Users + Employees
            DemoUserSeeder::class,
            DemoEmployeeSeeder::class,

            // Demo transactional data
            DemoShiftSeeder::class,
            DemoAttendanceSeeder::class,
            DemoLeaveSeeder::class,
            DemoPayrollSeeder::class,
            DemoWorkflowSeeder::class,
            DemoAssetSeeder::class,
            DemoRecruitmentSeeder::class,
            DemoOnboardingSeeder::class,
            DemoOffboardingSeeder::class,
            DemoNotificationSeeder::class,
            DemoReportingSeeder::class,
            DemoAuditSeeder::class,
        ]);
    }
}
