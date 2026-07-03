<?php

namespace App\Modules\Offboarding\Domain\Events;

use App\Modules\Offboarding\Domain\Aggregates\OffboardingRequest\OffboardingRequestId;

class OffboardingRequestApproved
{
    public function __construct(
        public readonly OffboardingRequestId $requestId,
        public readonly \DateTimeImmutable $approvedLastWorkingDate,
    ) {}
}
