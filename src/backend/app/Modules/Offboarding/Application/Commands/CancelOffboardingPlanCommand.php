<?php

namespace App\Modules\Offboarding\Application\Commands;

class CancelOffboardingPlanCommand
{
    public function __construct(public readonly string $planId) {}
}
