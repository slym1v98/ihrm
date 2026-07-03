<?php

namespace App\Modules\Offboarding\Infrastructure\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Identity\Infrastructure\Persistence\Eloquent\PermissionModel;
use App\Modules\Identity\Infrastructure\Persistence\Eloquent\RoleModel;
use App\Modules\Identity\Infrastructure\Persistence\Eloquent\RolePermissionModel;

class OffboardingPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['offboarding.request.view', 'request', 'view'], ['offboarding.request.create', 'request', 'create'],
            ['offboarding.request.update', 'request', 'update'], ['offboarding.request.submit', 'request', 'submit'],
            ['offboarding.request.approve', 'request', 'approve'], ['offboarding.request.reject', 'request', 'reject'],
            ['offboarding.plan.view', 'plan', 'view'], ['offboarding.plan.create', 'plan', 'create'],
            ['offboarding.plan.activate', 'plan', 'activate'], ['offboarding.plan.complete', 'plan', 'complete'],
            ['offboarding.task.view', 'task', 'view'], ['offboarding.task.create', 'task', 'create'],
            ['offboarding.task.update', 'task', 'update'], ['offboarding.task.start', 'task', 'start'],
            ['offboarding.task.complete', 'task', 'complete'], ['offboarding.task.waive', 'task', 'waive'],
            ['offboarding.clearance.complete', 'clearance', 'complete'],
        ];
        $codes = [];
        foreach ($permissions as [$code, $module, $action]) {
            $p = PermissionModel::firstOrCreate(['code' => $code], ['module' => $module, 'action' => $action, 'description' => "{$module}.{$action}"]);
            $codes[] = $p->code;
        }
        RoleModel::where('code', 'SUPER_ADMIN')->each(fn($r) => array_map(fn($c) => RolePermissionModel::firstOrCreate(['role_id' => $r->id, 'permission_code' => $c]), $codes));
    }
}
