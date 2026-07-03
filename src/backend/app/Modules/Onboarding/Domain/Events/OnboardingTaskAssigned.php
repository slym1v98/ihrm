<?php

namespace App\Modules\Onboarding\Domain\Events;

use App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlanId;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTaskId;

class OnboardingTaskAssigned
{
    public function __construct(
        public readonly ?OnboardingTaskId $taskId,
        public readonly ?OnboardingPlanId $planId,
        public readonly string $ownerType,
        public readonly string $ownerId,
        public readonly ?\DateTimeImmutable $dueDate,
    ) {}
}
