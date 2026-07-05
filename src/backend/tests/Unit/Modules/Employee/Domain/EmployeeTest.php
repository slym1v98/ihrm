<?php

namespace Tests\Unit\Modules\Employee\Domain;

use App\Modules\Employee\Application\Services\EmployeeLifecyclePolicy;
use App\Modules\Employee\Domain\Aggregates\Employee\Employee;
use App\Modules\Employee\Domain\Aggregates\Employee\EmployeeCode;
use App\Modules\Employee\Domain\Aggregates\Employee\EmployeeId;
use App\Modules\Employee\Domain\Aggregates\Employee\EmployeeStatus;
use App\Modules\Employee\Domain\Aggregates\Employee\PersonalName;
use App\Modules\Employee\Domain\Events\EmployeeCreated;
use App\Modules\Employee\Domain\Events\EmployeeStatusChanged;
use App\Modules\Employee\Domain\Exceptions\InvalidEmployeeStatusTransitionException;
use DateTimeImmutable;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    public function test_create_employee_emits_created_event(): void
    {
        $employee = Employee::create(EmployeeId::generate(), EmployeeCode::fromString('EMP001'), PersonalName::of('Ada', 'Lovelace'));

        $events = $employee->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(EmployeeCreated::class, $events[0]);
        $this->assertSame(EmployeeStatus::Draft, $employee->status());
    }

    public function test_invalid_status_transition_throws(): void
    {
        $employee = Employee::create(EmployeeId::generate(), EmployeeCode::fromString('EMP001'), PersonalName::of('Ada', 'Lovelace'));

        $this->expectException(InvalidEmployeeStatusTransitionException::class);
        $employee->changeStatus(EmployeeStatus::Resigned, new EmployeeLifecyclePolicy);
    }

    public function test_valid_status_transition_emits_event(): void
    {
        $employee = Employee::create(EmployeeId::generate(), EmployeeCode::fromString('EMP001'), PersonalName::of('Ada', 'Lovelace'));
        $employee->releaseEvents();

        $employee->changeStatus(EmployeeStatus::Active, new EmployeeLifecyclePolicy);

        $events = $employee->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(EmployeeStatusChanged::class, $events[0]);
    }

    public function test_change_employment_appends_history(): void
    {
        $employee = Employee::create(EmployeeId::generate(), EmployeeCode::fromString('EMP001'), PersonalName::of('Ada', 'Lovelace'));
        $employee->changeEmployment('b1', 'd1', 'p1', new DateTimeImmutable('2026-01-01'));

        $this->assertCount(1, $employee->history());
    }
}
