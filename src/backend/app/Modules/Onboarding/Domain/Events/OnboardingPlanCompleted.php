<?php

namespace App\Modules\Onboarding\Domain\Events;

use App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlanId;

class OnboardingPlanCompleted
{
    public function __construct(
        public readonly OnboardingPlanId $planId,
        public readonly string $employeeId,
    ) {}
}
