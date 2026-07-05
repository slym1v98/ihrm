<?php

namespace App\Modules\Audit\Infrastructure\Seeders;

use App\Modules\Audit\Infrastructure\Persistence\Eloquent\AuditLogModel;
use App\Modules\Identity\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Database\Seeder;

class DemoAuditSeeder extends Seeder
{
    public function run(): void
    {
        $admin = UserModel::where('email', 'admin@ihrm.local')->first();
        if (!$admin) return;

        $logs = [
            [
                'action' => 'login',
                'module' => 'auth',
                'entity_type' => 'user',
                'entity_id' => $admin->id,
                'actor_user_id' => $admin->id,
                'before_payload' => null,
                'after_payload' => ['ip' => '192.168.1.1'],
                'ip_address' => '192.168.1.1',
                'user_agent' => 'Chrome/120',
                'result' => 'success',
                'occurred_at' => now()->subHours(2),
            ],
            [
                'action' => 'employee_status_changed',
                'module' => 'employee',
                'entity_type' => 'employee',
                'entity_id' => $admin->id,
                'actor_user_id' => $admin->id,
                'before_payload' => ['status' => 'active'],
                'after_payload' => ['status' => 'suspended'],
                'ip_address' => '192.168.1.1',
                'result' => 'success',
                'occurred_at' => now()->subDays(1),
            ],
        ];

        foreach ($logs as $log) {
            AuditLogModel::create($log);
        }
    }
}
