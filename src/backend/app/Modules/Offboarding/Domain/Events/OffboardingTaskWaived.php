<?php

namespace App\Modules\Offboarding\Domain\Events;

class OffboardingTaskWaived
{
    public function __construct(
        public readonly string $taskId,
        public readonly string $planId,
        public readonly ?string $reason,
    ) {}
}
