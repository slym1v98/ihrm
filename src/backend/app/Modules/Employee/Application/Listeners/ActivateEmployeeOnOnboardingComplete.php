<?php

namespace App\Modules\Employee\Application\Listeners;

use App\Modules\Employee\Application\Services\EmployeeLifecyclePolicy;
use App\Modules\Employee\Domain\Aggregates\Employee\EmployeeId;
use App\Modules\Employee\Domain\Aggregates\Employee\EmployeeStatus;
use App\Modules\Employee\Domain\Repositories\EmployeeRepositoryInterface;
use App\Modules\Onboarding\Domain\Events\OnboardingPlanCompleted;

class ActivateEmployeeOnOnboardingComplete
{
    public function __construct(
        private EmployeeRepositoryInterface $employees,
        private EmployeeLifecyclePolicy $policy,
    ) {}

    public function handle(OnboardingPlanCompleted $event): void
    {
        $employee = $this->employees->findById(EmployeeId::fromString($event->employeeId));
        if ($employee === null) return;

        try {
            $employee->changeStatus(EmployeeStatus::Onboarding, $this->policy);
            $this->employees->saveAndDispatch($employee);
        } catch (\Throwable) {
            // Status transition may not be valid depending on current state
        }
    }
}
