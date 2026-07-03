<?php

namespace App\Modules\Onboarding\Application\Commands;

class RemoveOnboardingTaskCommand
{
    public function __construct(
        public readonly string $planId,
        public readonly string $taskId,
    ) {}
}
