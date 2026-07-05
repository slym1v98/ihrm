<?php

namespace App\Modules\Employee\Infrastructure\Seeders;

use App\Modules\Employee\Infrastructure\Persistence\Eloquent\ContractModel;
use App\Modules\Employee\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Modules\Identity\Infrastructure\Persistence\Eloquent\UserModel;
use App\Modules\Organization\Infrastructure\Persistence\Eloquent\BranchModel;
use App\Modules\Organization\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Modules\Organization\Infrastructure\Persistence\Eloquent\PositionModel;
use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class DemoEmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $branches = BranchModel::pluck('id', 'code')->all();
        $depts    = DepartmentModel::pluck('id', 'code')->all();
        $positions= PositionModel::pluck('id', 'code')->all();
        $users    = UserModel::pluck('id', 'email')->all();

        // [email, code, first, last, dob, gender, phone, branchCode, deptCode, posCode, salary, managerEmail]
        $employees = [
            ['hr.manager@ihrm.local',   'NV0001', 'Hồng',  'Nguyễn Thị',  '1985-03-14', 'female', '0912345001', 'HCM-HQ', 'HR',    'HR_MGR',    35_000_000, null],
            ['hr.lead@ihrm.local',      'NV0002', 'Hà',    'Trần Minh',   '1990-07-22', 'female', '0912345002', 'HCM-HQ', 'HR',    'HR_EXEC',   22_000_000, 'hr.manager@ihrm.local'],
            ['payroll.lead@ihrm.local', 'NV0003', 'Thu',   'Lê Thị',      '1988-11-05', 'female', '0912345003', 'HCM-HQ', 'ACC',   'MGR',       30_000_000, null],
            ['payroll.exec@ihrm.local', 'NV0004', 'Đức',   'Phạm Văn',    '1992-01-18', 'male',   '0912345004', 'HCM-HQ', 'ACC',   'ACCT',      18_000_000, 'payroll.lead@ihrm.local'],
            ['dev.lead@ihrm.local',     'NV0005', 'Long',  'Vũ Đình',     '1987-05-30', 'male',   '0912345005', 'HCM-HQ', 'IT-DEV','TL',        40_000_000, null],
            ['dev.senior@ihrm.local',   'NV0006', 'Bảo',   'Đỗ Quốc',     '1991-09-12', 'male',   '0912345006', 'HCM-HQ', 'IT-DEV','SR_DEV',    28_000_000, 'dev.lead@ihrm.local'],
            ['dev.junior@ihrm.local',   'NV0007', 'Tuấn',  'Bùi Anh',     '1998-02-25', 'male',   '0912345007', 'HCM-HQ', 'IT-DEV','DEV',      15_000_000, 'dev.lead@ihrm.local'],
            ['sales.mgr@ihrm.local',    'NV0008', 'Lan',   'Hoàng Thị',   '1986-08-08', 'female', '0912345008', 'HCM-HQ', 'SALES', 'MGR',       32_000_000, null],
            ['sales.exec@ihrm.local',   'NV0009', 'Nam',   'Ngô Văn',     '1994-04-19', 'male',   '0912345009', 'HCM-HQ', 'SALES', 'SALES_EXEC',14_000_000, 'sales.mgr@ihrm.local'],
            ['acct.exec@ihrm.local',    'NV0010', 'Trang', 'Đặng Thu',    '1993-06-11', 'female', '0912345010', 'HCM-HQ', 'ACC',   'ACCT',      16_000_000, 'payroll.lead@ihrm.local'],
            ['ops.exec@ihrm.local',     'NV0011', 'Hải',   'Trịnh Văn',   '1995-10-03', 'male',   '0912345011', 'HCM-HQ', 'IT-OPS','DEV',      17_000_000, 'dev.lead@ihrm.local'],
            ['hn.hr@ihrm.local',        'NV0012', 'Mai',   'Lý Thị',      '1989-12-27', 'female', '0912345012', 'HN-OFFICE','HN-HR','HR_EXEC', 20_000_000, 'hr.manager@ihrm.local'],
        ];

        $emailToEmpId = [];
        // Pass 1: create employees + contracts
        foreach ($employees as $row) {
            [$email,$code,$first,$last,$dob,$gender,$phone,$brCode,$dpCode,$posCode,$salary] = $row;
            $emp = EmployeeModel::updateOrCreate(
                ['employee_code' => $code],
                [
                                        'first_name'     => $first,
                    'last_name'      => $last,
                    'dob'            => $dob,
                    'gender'         => $gender,
                    'personal_email' => $email,
                    'phone'          => $phone,
                    'branch_id'      => $branches[$brCode] ?? null,
                    'department_id'  => $depts[$dpCode] ?? null,
                    'position_id'    => $positions[$posCode] ?? null,
                    'user_id'        => $users[$email] ?? null,
                    'status'         => 'active',
                ],
            );
            $emailToEmpId[$email] = $emp->id;

            ContractModel::updateOrCreate(
                ['contract_number' => 'HD-' . $code],
                [
                                        'employee_id' => $emp->id,
                    'contract_type' => 'permanent',
                    'start_date'  => '2024-01-01',
                    'end_date'    => null,
                    'sign_date'   => '2023-12-15',
                    'status'      => 'active',
                    'base_salary' => $salary,
                    'position_id' => $positions[$posCode] ?? null,
                ],
            );
        }

        // Pass 2: set manager_id
        foreach ($employees as $row) {
            $managerEmail = $row[11] ?? null;
            if ($managerEmail && isset($emailToEmpId[$row[0]], $emailToEmpId[$managerEmail])) {
                EmployeeModel::where('id', $emailToEmpId[$row[0]])->update(['manager_id' => $emailToEmpId[$managerEmail]]);
            }
        }
    }
}
