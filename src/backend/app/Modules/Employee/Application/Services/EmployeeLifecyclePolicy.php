<?php

namespace App\Modules\Employee\Application\Services;

use App\Modules\Employee\Domain\Aggregates\Employee\EmployeeStatus;

final class EmployeeLifecyclePolicy
{
    public function canTransition(EmployeeStatus $from, EmployeeStatus $to): bool
    {
        return in_array($to, $this->allowedTransitions()[$from->value] ?? [], true);
    }

    private function allowedTransitions(): array
    {
        return [
            EmployeeStatus::Draft->value => [EmployeeStatus::Onboarding, EmployeeStatus::Active],
            EmployeeStatus::Onboarding->value => [EmployeeStatus::Probation],
            EmployeeStatus::Probation->value => [EmployeeStatus::Active, EmployeeStatus::Resigned],
            EmployeeStatus::Active->value => [EmployeeStatus::Suspended, EmployeeStatus::Resigned],
            EmployeeStatus::Suspended->value => [EmployeeStatus::Active],
            EmployeeStatus::Resigned->value => [EmployeeStatus::Archived],
        ];
    }
}
