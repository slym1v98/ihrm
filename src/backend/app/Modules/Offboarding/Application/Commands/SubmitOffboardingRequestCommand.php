<?php

namespace App\Modules\Offboarding\Application\Commands;

class SubmitOffboardingRequestCommand
{
    public function __construct(
        public readonly string $requestId,
        public readonly ?string $workflowTemplateId = null,
    ) {}
}
