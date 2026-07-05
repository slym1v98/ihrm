<?php

namespace App\Modules\Recruitment\Infrastructure\Seeders;

use App\Modules\Identity\Infrastructure\Persistence\Eloquent\UserModel;
use App\Modules\Organization\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Modules\Recruitment\Infrastructure\Persistence\Eloquent\CandidateModel;
use App\Modules\Recruitment\Infrastructure\Persistence\Eloquent\RecruitmentRequisitionModel;
use Illuminate\Database\Seeder;

class DemoRecruitmentSeeder extends Seeder
{
    public function run(): void
    {
        $hrDept = DepartmentModel::where('code', 'IT-DEV')->first();
        $salesDept = DepartmentModel::where('code', 'SALES')->first();
        $adminUser = UserModel::where('email', 'admin@ihrm.local')->first();

        if (! $hrDept || ! $adminUser) {
            return;
        }

        $requisitions = [
            [
                'department_id' => $hrDept->id,
                'position' => 'Senior PHP Developer',
                'headcount' => 2,
                'reason' => 'Mở rộng đội ngũ phát triển',
                'status' => 'open',
                'opened_at' => now()->subDays(10),
                'created_by' => $adminUser->id,
            ],
            [
                'department_id' => $salesDept?->id ?? $hrDept->id,
                'position' => 'Sales Executive',
                'headcount' => 1,
                'reason' => 'Thay thế nhân viên nghỉ việc',
                'status' => 'open',
                'opened_at' => now()->subDays(5),
                'created_by' => $adminUser->id,
            ],
        ];

        foreach ($requisitions as $req) {
            $model = RecruitmentRequisitionModel::firstOrCreate(
                ['department_id' => $req['department_id'], 'position' => $req['position']],
                $req,
            );

            $candidates = [
                ['full_name' => 'Nguyễn Văn A', 'email' => 'candidate.a.'.substr($model->id, 0, 8).'@gmail.com', 'phone' => '0901111001', 'source' => 'linkedin', 'status' => 'new'],
                ['full_name' => 'Trần Thị B', 'email' => 'candidate.b.'.substr($model->id, 0, 8).'@gmail.com', 'phone' => '0901111002', 'source' => 'referral', 'status' => 'interview'],
            ];

            foreach ($candidates as $c) {
                CandidateModel::updateOrCreate(
                    ['email' => $c['email']],
                    array_merge($c, ['requisition_id' => $model->id]),
                );
            }
        }
    }
}
