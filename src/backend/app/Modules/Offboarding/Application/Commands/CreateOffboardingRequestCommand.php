<?php

namespace App\Modules\Offboarding\Application\Commands;

class CreateOffboardingRequestCommand
{
    public function __construct(
        public readonly string $employeeId, public readonly string $type, public readonly string $reason, public readonly string $requestedLastWorkingDate,
    ) {}
}
