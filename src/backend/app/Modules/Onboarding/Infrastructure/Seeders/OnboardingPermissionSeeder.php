<?php

namespace App\Modules\Onboarding\Infrastructure\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Identity\Infrastructure\Persistence\Eloquent\PermissionModel;

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

        foreach ($permissions as [$code, $module, $action]) {
            PermissionModel::updateOrCreate(
                ['code' => $code],
                [
                    'module' => $module,
                    'action' => $action,
                    'description' => "{$module}.{$action}",
                ],
            );
        }
    }
}
