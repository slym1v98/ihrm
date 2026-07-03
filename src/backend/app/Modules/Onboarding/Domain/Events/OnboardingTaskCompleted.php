<?php

namespace App\Modules\Onboarding\Domain\Events;

use App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlanId;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTaskId;

class OnboardingTaskCompleted
{
    public function __construct(
        public readonly ?OnboardingTaskId $taskId,
        public readonly string $planId,
        public readonly ?string $proofFileObjectId,
    ) {}
}
