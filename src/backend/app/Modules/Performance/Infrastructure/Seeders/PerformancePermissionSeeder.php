<?php

namespace App\Modules\Performance\Infrastructure\Seeders;

use App\Modules\Identity\Infrastructure\Persistence\Eloquent\PermissionModel;
use App\Modules\Identity\Infrastructure\Persistence\Eloquent\RoleModel;
use App\Modules\Identity\Infrastructure\Persistence\Eloquent\RolePermissionModel;
use Illuminate\Database\Seeder;

class PerformancePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['performance.cycle.view', 'cycle', 'view'], ['performance.cycle.create', 'cycle', 'create'],
            ['performance.cycle.update', 'cycle', 'update'], ['performance.cycle.activate', 'cycle', 'activate'],
            ['performance.cycle.complete', 'cycle', 'complete'], ['performance.cycle.cancel', 'cycle', 'cancel'],
            ['performance.review.view', 'review', 'view'], ['performance.review.create', 'review', 'create'],
            ['performance.review.submit_self', 'review', 'submit_self'], ['performance.review.submit_manager', 'review', 'submit_manager'],
            ['performance.review.submit_hr', 'review', 'submit_hr'], ['performance.review.finalize', 'review', 'finalize'],
            ['performance.goal.view', 'goal', 'view'], ['performance.goal.create', 'goal', 'create'],
            ['performance.goal.update', 'goal', 'update'], ['performance.goal.complete', 'goal', 'complete'],
            ['performance.template.view', 'template', 'view'], ['performance.template.create', 'template', 'create'],
            ['performance.template.update', 'template', 'update'], ['performance.template.delete', 'template', 'delete'],
        ];
        $codes = [];
        foreach ($permissions as [$code, $module, $action]) {
            $p = PermissionModel::firstOrCreate(['code' => $code], ['module' => $module, 'action' => $action, 'description' => "{$module}.{$action}"]);
            $codes[] = $p->code;
        }
        RoleModel::where('code', 'SUPER_ADMIN')->each(fn ($r) => array_map(fn ($c) => RolePermissionModel::firstOrCreate(['role_id' => $r->id, 'permission_code' => $c]), $codes));
    }
}
