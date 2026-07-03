<?php

namespace App\Modules\Offboarding\Domain\Events;

use App\Modules\Offboarding\Domain\Aggregates\OffboardingRequest\OffboardingRequestId;

class OffboardingRequestSubmitted
{
    public function __construct(
        public readonly OffboardingRequestId $requestId,
    ) {}
}
