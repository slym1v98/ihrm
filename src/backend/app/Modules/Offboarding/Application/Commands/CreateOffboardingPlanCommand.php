<?php

namespace App\Modules\Offboarding\Application\Commands;

class CreateOffboardingPlanCommand
{
    public function __construct(
        public readonly string $employeeId,
        public readonly ?string $candidateId,
        public readonly ?string $templateId,
        public readonly string $startDate,
    ) {}
}
