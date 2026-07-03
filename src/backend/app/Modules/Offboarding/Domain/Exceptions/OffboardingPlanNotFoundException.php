<?php

namespace App\Modules\Offboarding\Domain\Exceptions;

class OffboardingPlanNotFoundException extends \RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct("Offboarding plan not found: {$id}");
    }
}
