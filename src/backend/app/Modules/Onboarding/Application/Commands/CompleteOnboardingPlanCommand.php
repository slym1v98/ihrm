<?php

namespace App\Modules\Onboarding\Application\Commands;

class CompleteOnboardingPlanCommand
{
    public function __construct(
        public readonly string $planId,
        public readonly ?string $workflowTemplateId = null,
    ) {}
}
