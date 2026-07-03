<?php

namespace App\Modules\Onboarding\Application\Commands;

class CancelOnboardingPlanCommand
{
    public function __construct(public readonly string $planId) {}
}
