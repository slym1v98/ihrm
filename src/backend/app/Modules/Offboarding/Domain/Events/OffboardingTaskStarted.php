<?php

namespace App\Modules\Offboarding\Domain\Events;

class OffboardingTaskStarted
{
    public function __construct(
        public readonly string $taskId,
        public readonly string $planId,
    ) {}
}
