<?php

namespace App\Modules\Offboarding\Domain\Events;

use App\Modules\Offboarding\Domain\Aggregates\OffboardingPlan\OffboardingPlanId;

class OffboardingPlanCreated
{
    public function __construct(
        public readonly OffboardingPlanId $planId,
        public readonly string $requestId,
    ) {}
}
