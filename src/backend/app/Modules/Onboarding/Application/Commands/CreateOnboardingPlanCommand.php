<?php

namespace App\Modules\Onboarding\Application\Commands;

class CreateOnboardingPlanCommand
{
    public function __construct(
        public readonly string $employeeId,
        public readonly ?string $candidateId,
        public readonly ?string $templateId,
        public readonly string $startDate,
    ) {}
}
