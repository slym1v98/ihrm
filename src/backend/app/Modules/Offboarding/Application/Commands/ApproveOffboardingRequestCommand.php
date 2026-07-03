<?php

namespace App\Modules\Offboarding\Application\Commands;

class ApproveOffboardingRequestCommand
{
    public function __construct(
        public readonly string $requestId, public readonly string $approvedLastWorkingDate,
    ) {}
}
