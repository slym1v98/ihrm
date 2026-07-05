<?php

namespace App\Modules\Employee\Application\Listeners;

use App\Modules\Employee\Domain\Events\EmployeeStatusChanged;
use App\Modules\Employee\Domain\Repositories\EmployeeRepositoryInterface;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingRequest\OffboardingRequest;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingRequest\OffboardingRequestId;
use App\Modules\Offboarding\Domain\Repositories\OffboardingRequestRepositoryInterface;
use App\Modules\Offboarding\Domain\ValueObjects\OffboardingRequestType;

class CreateOffboardingOnResign
{
    public function __construct(
        private EmployeeRepositoryInterface $employees,
        private OffboardingRequestRepositoryInterface $offboardingRequests,
    ) {}

    public function handle(EmployeeStatusChanged $event): void
    {
        if ($event->newStatus !== 'resigned' && $event->newStatus !== 'terminated') {
            return;
        }

        $employee = $this->employees->findById($event->employeeId);
        if ($employee === null) {
            return;
        }

        $offboarding = OffboardingRequest::create(
            OffboardingRequestId::generate(),
            (string) $event->employeeId->value,
            OffboardingRequestType::Resignation,
            $event->reason ?? 'Employee resigned',
            new \DateTimeImmutable('now'),
        );

        $offboarding->submit();
        $this->offboardingRequests->save($offboarding);
    }
}
