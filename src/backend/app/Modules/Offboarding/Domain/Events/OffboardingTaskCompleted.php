<?php

namespace App\Modules\Offboarding\Domain\Events;

class OffboardingTaskCompleted
{
    public function __construct(
        public readonly string $taskId,
        public readonly string $planId,
        public readonly ?string $proofFileObjectId,
    ) {}
}
