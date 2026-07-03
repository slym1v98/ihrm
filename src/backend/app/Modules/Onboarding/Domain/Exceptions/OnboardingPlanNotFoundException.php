<?php

namespace App\Modules\Onboarding\Domain\Exceptions;

class OnboardingPlanNotFoundException extends \RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct("Onboarding plan not found: {$id}");
    }
}
