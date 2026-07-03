<?php

namespace App\Modules\Offboarding\Application\Commands;

class RejectOffboardingRequestCommand
{
    public function __construct(
        public readonly string $requestId,
        public readonly ?string $reason = null,
    ) {}
}
