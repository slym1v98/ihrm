<?php

namespace App\Modules\Offboarding\Application\Commands;

class CreateOffboardingPlanCommand
{
    public function __construct(
        public readonly string $employeeId,
        public readonly string $startDate,
        public readonly ?string $candidateId = null,
        public readonly ?string $templateId = null,
    ) {}
}
