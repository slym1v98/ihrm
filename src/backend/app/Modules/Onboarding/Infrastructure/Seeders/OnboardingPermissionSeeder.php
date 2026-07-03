<?php

namespace App\Modules\Onboarding\Infrastructure\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Identity\Infrastructure\Persistence\Eloquent\PermissionModel;
use App\Modules\Identity\Infrastructure\Persistence\Eloquent\RoleModel;
use App\Modules\Identity\Infrastructure\Persistence\Eloquent\RolePermissionModel;

class OnboardingPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['onboarding.template.view', 'template', 'view'],
            ['onboarding.template.create', 'template', 'create'],
            ['onboarding.template.update', 'template', 'update'],
            ['onboarding.template.delete', 'template', 'delete'],
            ['onboarding.plan.view', 'plan', 'view'],
            ['onboarding.plan.create', 'plan', 'create'],
            ['onboarding.plan.update', 'plan', 'update'],
            ['onboarding.plan.activate', 'plan', 'activate'],
            ['onboarding.plan.cancel', 'plan', 'cancel'],
            ['onboarding.plan.complete', 'plan', 'complete'],
            ['onboarding.task.view', 'task', 'view'],
            ['onboarding.task.create', 'task', 'create'],
            ['onboarding.task.update', 'task', 'update'],
            ['onboarding.task.start', 'task', 'start'],
            ['onboarding.task.complete', 'task', 'complete'],
            ['onboarding.task.waive', 'task', 'waive'],
        ];

        $createdCodes = [];
        foreach ($permissions as [$code, $module, $action]) {
            $perm = PermissionModel::firstOrCreate(
                ['code' => $code],
                [
                    'module' => $module,
                    'action' => $action,
                    'description' => "{$module}.{$action}",
                ],
            );
            $createdCodes[] = $perm->code;
        }

        // Grant all onboarding permissions to SUPER_ADMIN role
        RoleModel::where('code', 'SUPER_ADMIN')->each(function (RoleModel $role) use ($createdCodes) {
            foreach ($createdCodes as $code) {
                RolePermissionModel::firstOrCreate([
                    'role_id' => $role->id,
                    'permission_code' => $code,
                ]);
            }
        });
    }
}
