<?php

namespace App\Modules\Onboarding\Infrastructure\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Identity\Infrastructure\Persistence\Eloquent\PermissionModel;

class OnboardingPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['onboarding.template.view', 'template.view', 'View onboarding templates'],
            ['onboarding.template.create', 'template.create', 'Create onboarding templates'],
            ['onboarding.template.update', 'template.update', 'Update onboarding templates'],
            ['onboarding.template.delete', 'template.delete', 'Delete onboarding templates'],
            ['onboarding.plan.view', 'plan.view', 'View onboarding plans'],
            ['onboarding.plan.create', 'plan.create', 'Create onboarding plans'],
            ['onboarding.plan.update', 'plan.update', 'Update onboarding plans'],
            ['onboarding.plan.activate', 'plan.activate', 'Activate onboarding plans'],
            ['onboarding.plan.cancel', 'plan.cancel', 'Cancel onboarding plans'],
            ['onboarding.plan.complete', 'plan.complete', 'Complete onboarding plans'],
            ['onboarding.task.view', 'task.view', 'View onboarding tasks'],
            ['onboarding.task.create', 'task.create', 'Create onboarding tasks'],
            ['onboarding.task.update', 'task.update', 'Update onboarding tasks'],
            ['onboarding.task.start', 'task.start', 'Start onboarding tasks'],
            ['onboarding.task.complete', 'task.complete', 'Complete onboarding tasks'],
            ['onboarding.task.waive', 'task.waive', 'Waive onboarding tasks'],
        ];

        foreach ($permissions as [$code, $name, $description]) {
            PermissionModel::updateOrCreate(
                ['code' => $code],
                ['name' => $name, 'description' => $description],
            );
        }
    }
}
