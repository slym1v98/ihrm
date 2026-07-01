<?php

namespace App\Modules\Employee\Domain\Aggregates\Employee;

use App\Modules\Employee\Application\Services\EmployeeLifecyclePolicy;
use App\Modules\Employee\Domain\Events\EmployeeCreated;
use App\Modules\Employee\Domain\Events\EmployeeEmploymentChanged;
use App\Modules\Employee\Domain\Events\EmployeeManagerChanged;
use App\Modules\Employee\Domain\Events\EmployeePersonalInfoUpdated;
use App\Modules\Employee\Domain\Events\EmployeeStatusChanged;
use App\Modules\Employee\Domain\Exceptions\InvalidEmployeeStatusTransitionException;
use DateTimeImmutable;

final class Employee
{
    /** @var object[] */
    private array $recordedEvents = [];

    /** @param EmploymentSnapshot[] $history */
    private function __construct(
        private readonly EmployeeId $id,
        private readonly EmployeeCode $code,
        private PersonalName $name,
        private ?DateTimeImmutable $dob,
        private ?string $gender,
        private ?string $personalEmail,
        private ?string $phone,
        private ?Address $address,
        private EmployeeStatus $status,
        private ?EmployeeId $managerId = null,
        private ?string $branchId = null,
        private ?string $departmentId = null,
        private ?string $positionId = null,
        private ?string $userId = null,
        private array $history = [],
    ) {}

    public static function create(EmployeeId $id, EmployeeCode $code, PersonalName $name): self
    {
        $employee = new self($id, $code, $name, null, null, null, null, null, EmployeeStatus::Draft);
        $employee->record(new EmployeeCreated($id, $code->value, $name->full(), EmployeeStatus::Draft->value, new DateTimeImmutable()));
        return $employee;
    }

    public function updatePersonalInfo(PersonalName $name, ?DateTimeImmutable $dob, ?string $gender, ?string $personalEmail, ?string $phone, ?Address $address): void
    {
        $changed = [];
        if ($this->name->full() !== $name->full()) {
            $changed[] = 'name';
        }
        $this->name = $name;
        $this->dob = $dob;
        $this->gender = $gender;
        $this->personalEmail = $personalEmail;
        $this->phone = $phone;
        $this->address = $address;
        $this->record(new EmployeePersonalInfoUpdated($this->id, $changed, new DateTimeImmutable()));
    }

    public function changeEmployment(?string $branchId, ?string $departmentId, ?string $positionId, ?DateTimeImmutable $effectiveAt = null): void
    {
        $this->branchId = $branchId;
        $this->departmentId = $departmentId;
        $this->positionId = $positionId;
        $snapshot = new EmploymentSnapshot($branchId, $departmentId, $positionId, $effectiveAt ?? new DateTimeImmutable());
        $this->history[] = $snapshot;
        $this->record(new EmployeeEmploymentChanged($this->id, $branchId, $departmentId, $positionId, new DateTimeImmutable()));
    }

    public function changeManager(?EmployeeId $managerId): void
    {
        $old = $this->managerId;
        $this->managerId = $managerId;
        $this->record(new EmployeeManagerChanged($this->id, $old, $managerId, new DateTimeImmutable()));
    }

    public function changeStatus(EmployeeStatus $newStatus, EmployeeLifecyclePolicy $policy, ?string $reason = null): void
    {
        if (! $policy->canTransition($this->status, $newStatus)) {
            throw new InvalidEmployeeStatusTransitionException($this->status->value, $newStatus->value);
        }
        $old = $this->status;
        $this->status = $newStatus;
        $this->record(new EmployeeStatusChanged($this->id, $old->value, $newStatus->value, $reason, new DateTimeImmutable()));
    }

    public function linkUserAccount(string $userId): void
    {
        $this->userId = $userId;
    }

    public function id(): EmployeeId { return $this->id; }
    public function code(): EmployeeCode { return $this->code; }
    public function name(): PersonalName { return $this->name; }
    public function status(): EmployeeStatus { return $this->status; }
    public function history(): array { return $this->history; }

    public function releaseEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];
        return $events;
    }

    private function record(object $event): void
    {
        $this->recordedEvents[] = $event;
    }
}
