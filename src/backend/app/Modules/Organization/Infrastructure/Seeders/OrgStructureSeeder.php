<?php

namespace App\Modules\Organization\Infrastructure\Seeders;

use App\Modules\Organization\Infrastructure\Persistence\Eloquent\BranchModel;
use App\Modules\Organization\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Modules\Organization\Infrastructure\Persistence\Eloquent\PositionModel;
use Illuminate\Database\Seeder;

class OrgStructureSeeder extends Seeder
{
    public function run(): void
    {
        $hcm = BranchModel::updateOrCreate(
            ['code' => 'HCM-HQ'],
            ['name' => 'Ho Chi Minh Head Office', 'address' => '123 Nguyen Hue, District 1, HCMC', 'phone' => '02838223344', 'email' => 'hcm@ihrm.local', 'status' => 'active']
        );

        $hn = BranchModel::updateOrCreate(
            ['code' => 'HN-OFFICE'],
            ['name' => 'Ha Noi Office', 'address' => '456 Tran Hung Dao, Hoan Kiem, Hanoi', 'phone' => '02439332244', 'email' => 'hn@ihrm.local', 'status' => 'active']
        );

        DepartmentModel::updateOrCreate(['branch_id' => $hcm->id, 'code' => 'BOARD'], ['name' => 'Ban Giam Doc', 'parent_id' => null, 'status' => 'active']);
        DepartmentModel::updateOrCreate(['branch_id' => $hcm->id, 'code' => 'HR'], ['name' => 'Nhan Su', 'parent_id' => null, 'status' => 'active']);
        DepartmentModel::updateOrCreate(['branch_id' => $hcm->id, 'code' => 'ACC'], ['name' => 'Ke Toan', 'parent_id' => null, 'status' => 'active']);
        $it = DepartmentModel::updateOrCreate(['branch_id' => $hcm->id, 'code' => 'IT'], ['name' => 'Ky Thuat', 'parent_id' => null, 'status' => 'active']);
        DepartmentModel::updateOrCreate(['branch_id' => $hcm->id, 'code' => 'SALES'], ['name' => 'Kinh Doanh', 'parent_id' => null, 'status' => 'active']);
        DepartmentModel::updateOrCreate(['branch_id' => $hcm->id, 'code' => 'IT-DEV'], ['name' => 'Phong Phat Trien', 'parent_id' => $it->id, 'status' => 'active']);
        DepartmentModel::updateOrCreate(['branch_id' => $hcm->id, 'code' => 'IT-OPS'], ['name' => 'Phong Van Hanh', 'parent_id' => $it->id, 'status' => 'active']);
        DepartmentModel::updateOrCreate(['branch_id' => $hn->id, 'code' => 'HN-HR'], ['name' => 'Nhan Su HN', 'parent_id' => null, 'status' => 'active']);
        DepartmentModel::updateOrCreate(['branch_id' => $hn->id, 'code' => 'HN-ACC'], ['name' => 'Ke Toan HN', 'parent_id' => null, 'status' => 'active']);

        foreach ([
            ['code' => 'DEV', 'name' => 'Developer', 'level' => 3],
            ['code' => 'SR_DEV', 'name' => 'Senior Developer', 'level' => 4],
            ['code' => 'TL', 'name' => 'Team Leader', 'level' => 5],
            ['code' => 'HR_EXEC', 'name' => 'HR Executive', 'level' => 3],
            ['code' => 'HR_MGR', 'name' => 'HR Manager', 'level' => 5],
            ['code' => 'ACCT', 'name' => 'Accountant', 'level' => 3],
            ['code' => 'SALES_EXEC', 'name' => 'Sales Executive', 'level' => 3],
            ['code' => 'MGR', 'name' => 'General Manager', 'level' => 6],
        ] as $position) {
            PositionModel::updateOrCreate(['code' => $position['code']], $position + ['status' => 'active']);
        }
    }
}
