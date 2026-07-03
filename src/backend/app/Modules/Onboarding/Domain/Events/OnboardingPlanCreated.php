<?php

namespace App\Modules\Onboarding\Domain\Events;

use App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlanId;

class OnboardingPlanCreated
{
    public function __construct(
        public readonly OnboardingPlanId $planId,
        public readonly string $employeeId,
        public readonly \DateTimeImmutable $startDate,
    ) {}
}
