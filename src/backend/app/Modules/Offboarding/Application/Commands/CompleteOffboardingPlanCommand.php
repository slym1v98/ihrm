<?php

namespace App\Modules\Offboarding\Application\Commands;

class CompleteOffboardingPlanCommand
{
    public function __construct(
        public readonly string $planId,
        public readonly ?string $workflowTemplateId = null,
    ) {}
}
