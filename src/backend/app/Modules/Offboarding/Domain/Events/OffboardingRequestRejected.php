<?php

namespace App\Modules\Offboarding\Domain\Events;

use App\Modules\Offboarding\Domain\Aggregates\OffboardingRequest\OffboardingRequestId;

class OffboardingRequestRejected
{
    public function __construct(
        public readonly OffboardingRequestId $requestId,
        public readonly ?string $reason,
    ) {}
}
