<?php

namespace App\Modules\Offboarding\Infrastructure\Seeders;

use App\Modules\Employee\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Modules\Offboarding\Infrastructure\Persistence\Eloquent\OffboardingPlanModel;
use App\Modules\Offboarding\Infrastructure\Persistence\Eloquent\OffboardingTaskModel;
use App\Modules\Offboarding\Infrastructure\Persistence\Eloquent\OffboardingRequestModel;
use App\Modules\Identity\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Database\Seeder;

class DemoOffboardingSeeder extends Seeder
{
    public function run(): void
    {
        $employee = EmployeeModel::where('status', 'active')->skip(5)->first();
        $admin = UserModel::where('email', 'admin@ihrm.local')->first();

        if (!$employee || !$admin) return;

        $request = OffboardingRequestModel::firstOrCreate(
            ['employee_id' => $employee->id],
            [
                'type' => 'resignation',
                'reason' => 'Cơ hội nghề nghiệp mới',
                'requested_last_working_date' => now()->addDays(30)->toDateString(),
                'status' => 'pending',
            ],
        );

        $plan = OffboardingPlanModel::firstOrCreate(
            ['offboarding_request_id' => $request->id],
            ['status' => 'pending']
        );

        $tasks = [
            ['title' => 'Bàn giao tài sản IT', 'task_type' => 'it_handover', 'owner_type' => 'it', 'owner_id' => $admin->id, 'sort_order' => 1, 'status' => 'pending'],
            ['title' => 'Thanh lý hợp đồng', 'task_type' => 'contract_close', 'owner_type' => 'hr', 'owner_id' => $admin->id, 'sort_order' => 2, 'status' => 'pending'],
            ['title' => 'Phỏng vấn thôi việc', 'task_type' => 'exit_interview', 'owner_type' => 'hr', 'owner_id' => $admin->id, 'sort_order' => 3, 'status' => 'pending'],
        ];

        foreach ($tasks as $t) {
            OffboardingTaskModel::firstOrCreate(
                ['offboarding_plan_id' => $plan->id, 'title' => $t['title']],
                $t,
            );
        }
    }
}
