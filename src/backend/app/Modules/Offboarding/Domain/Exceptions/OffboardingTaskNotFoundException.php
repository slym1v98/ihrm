<?php

namespace App\Modules\Offboarding\Domain\Exceptions;

class OffboardingTaskNotFoundException extends \RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct("Offboarding task not found: {$id}");
    }
}
