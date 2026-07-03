<?php

namespace App\Modules\Onboarding\Domain\Exceptions;

class OnboardingTaskNotFoundException extends \RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct("Onboarding task not found: {$id}");
    }
}
