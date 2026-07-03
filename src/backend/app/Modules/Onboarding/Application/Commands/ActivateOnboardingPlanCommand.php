<?php

namespace App\Modules\Onboarding\Application\Commands;

class ActivateOnboardingPlanCommand
{
    public function __construct(public readonly string $planId) {}
}
