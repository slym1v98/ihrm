<?php

namespace App\Modules\Onboarding\Infrastructure\Seeders;

use App\Modules\Employee\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Modules\Identity\Infrastructure\Persistence\Eloquent\UserModel;
use App\Modules\Onboarding\Infrastructure\Persistence\Eloquent\OnboardingPlanModel;
use App\Modules\Onboarding\Infrastructure\Persistence\Eloquent\OnboardingTaskModel;
use App\Modules\Onboarding\Infrastructure\Persistence\Eloquent\OnboardingTemplateModel;
use Illuminate\Database\Seeder;

class DemoOnboardingSeeder extends Seeder
{
    public function run(): void
    {
        OnboardingTemplateModel::updateOrCreate(
            ['code' => 'ONBRD-STD'],
            ['name' => 'Onboarding tiêu chuẩn', 'rules' => ['steps' => ['IT Setup', 'HR Orientation', 'Dept Onboarding', 'Company Tour']], 'active' => true]
        );

        $template = OnboardingTemplateModel::where('code', 'ONBRD-STD')->first();
        $employee = EmployeeModel::where('status', 'active')->first();

        $admin = UserModel::where('email', 'admin@ihrm.local')->first();
        if (! $template || ! $employee || ! $admin) {
            return;
        }

        $plan = OnboardingPlanModel::firstOrCreate(
            ['employee_id' => $employee->id, 'template_id' => $template->id],
            ['start_date' => now()->subDays(7)->toDateString(), 'status' => 'in_progress']
        );

        $tasks = [
            ['title' => 'Cấp tài khoản IT', 'task_type' => 'it_setup', 'owner_type' => 'system', 'owner_id' => $admin->id, 'sort_order' => 1, 'status' => 'completed'],
            ['title' => 'Định hướng HR', 'task_type' => 'hr_orientation', 'owner_type' => 'system', 'owner_id' => $admin->id, 'sort_order' => 2, 'status' => 'completed'],
            ['title' => 'Giới thiệu phòng ban', 'task_type' => 'dept_intro', 'owner_type' => 'manager', 'owner_id' => $admin->id, 'sort_order' => 3, 'status' => 'pending'],
            ['title' => 'Tham quan văn phòng', 'task_type' => 'office_tour', 'owner_type' => 'hr', 'owner_id' => $admin->id, 'sort_order' => 4, 'status' => 'pending'],
        ];

        foreach ($tasks as $t) {
            OnboardingTaskModel::firstOrCreate(
                ['onboarding_plan_id' => $plan->id, 'title' => $t['title']],
                $t,
            );
        }
    }
}
