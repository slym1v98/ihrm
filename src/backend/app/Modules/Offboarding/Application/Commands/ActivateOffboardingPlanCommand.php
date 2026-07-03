<?php

namespace App\Modules\Offboarding\Application\Commands;

class ActivateOffboardingPlanCommand
{
    public function __construct(public readonly string $planId) {}
}
