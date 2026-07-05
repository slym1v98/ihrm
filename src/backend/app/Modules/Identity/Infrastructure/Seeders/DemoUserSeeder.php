<?php

namespace App\Modules\Identity\Infrastructure\Seeders;

use App\Modules\Identity\Infrastructure\Persistence\Eloquent\RoleModel;
use App\Modules\Identity\Infrastructure\Persistence\Eloquent\UserModel;
use App\Modules\Identity\Infrastructure\Persistence\Eloquent\UserRoleModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    /**
     * Seed demo users covering all roles.
     * Password mặc định: password
     */
    public function run(): void
    {
        $roles = RoleModel::pluck('id', 'code')->all();
        $password = Hash::make('password');

        $users = [
            // HR Manager
            ['hr.manager@ihrm.local',   'Nguyễn Thị Hồng',   'HR_MANAGER'],
            ['hr.lead@ihrm.local',      'Trần Minh Hà',      'HR_MANAGER'],
            // Payroll
            ['payroll.lead@ihrm.local', 'Lê Thị Thu',        'PAYROLL'],
            ['payroll.exec@ihrm.local', 'Phạm Văn Đức',      'PAYROLL'],
            // Employees (self-service)
            ['dev.lead@ihrm.local',     'Vũ Đình Long',      'EMPLOYEE'],
            ['dev.senior@ihrm.local',   'Đỗ Quốc Bảo',       'EMPLOYEE'],
            ['dev.junior@ihrm.local',   'Bùi Anh Tuấn',      'EMPLOYEE'],
            ['sales.mgr@ihrm.local',    'Hoàng Thị Lan',     'EMPLOYEE'],
            ['sales.exec@ihrm.local',   'Ngô Văn Nam',       'EMPLOYEE'],
            ['acct.exec@ihrm.local',    'Đặng Thu Trang',    'EMPLOYEE'],
            ['ops.exec@ihrm.local',     'Trịnh Văn Hải',     'EMPLOYEE'],
            ['hn.hr@ihrm.local',        'Lý Thị Mai',        'EMPLOYEE'],
        ];

        foreach ($users as [$email, $name, $roleCode]) {
            $user = UserModel::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => $password,
                    'status' => 'active',
                ],
            );

            $roleId = $roles[$roleCode] ?? null;
            if ($roleId && ! UserRoleModel::where('user_id', $user->id)->where('role_id', $roleId)->whereNull('revoked_at')->exists()) {
                UserRoleModel::create([
                    'user_id' => $user->id,
                    'role_id' => $roleId,
                    'assigned_at' => now(),
                ]);
            }
        }
    }
}
