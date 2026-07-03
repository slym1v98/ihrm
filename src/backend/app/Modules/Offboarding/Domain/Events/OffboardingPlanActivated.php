<?php

namespace App\Modules\Offboarding\Domain\Events;

use App\Modules\Offboarding\Domain\Aggregates\OffboardingPlan\OffboardingPlanId;

class OffboardingPlanActivated
{
    public function __construct(
        public readonly OffboardingPlanId $planId,
    ) {}
}
