<?php

namespace App\Modules\Onboarding\Domain\Exceptions;

class OnboardingTemplateNotFoundException extends \RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct("Onboarding template not found: {$id}");
    }
}
