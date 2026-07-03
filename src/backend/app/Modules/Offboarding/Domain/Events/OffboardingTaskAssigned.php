<?php

namespace App\Modules\Offboarding\Domain\Events;

class OffboardingTaskAssigned
{
    public function __construct(
        public readonly string $taskId,
        public readonly string $planId,
        public readonly string $ownerType,
        public readonly string $ownerId,
    ) {}
}
