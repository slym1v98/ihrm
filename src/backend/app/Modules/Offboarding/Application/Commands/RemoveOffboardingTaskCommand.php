<?php

namespace App\Modules\Offboarding\Application\Commands;

class RemoveOffboardingTaskCommand
{
    public function __construct(
        public readonly string $planId,
        public readonly string $taskId,
    ) {}
}
