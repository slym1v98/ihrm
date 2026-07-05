<?php

namespace App\Modules\Training\Domain\Aggregates\TrainingEnrollment;

use App\Modules\Training\Domain\Events\AttendanceRecorded;
use App\Modules\Training\Domain\Events\EmployeeEnrolled;
use App\Modules\Training\Domain\Events\EnrollmentCancelled;
use App\Modules\Training\Domain\Exceptions\InvalidEnrollmentStatusException;
use App\Modules\Training\Domain\ValueObjects\EnrollmentStatus;

class TrainingEnrollment
{
    private array $recordedEvents = [];

    private function __construct(
        private readonly TrainingEnrollmentId $id, private readonly string $sessionId,
        private readonly string $employeeId, private \DateTimeImmutable $enrolledAt,
        private ?array $attendance, private EnrollmentStatus $status,
    ) {}

    public static function enroll(TrainingEnrollmentId $id, string $sessionId, string $employeeId, \DateTimeImmutable $enrolledAt): self
    {
        $e = new self($id, $sessionId, $employeeId, $enrolledAt, null, EnrollmentStatus::Enrolled);
        $e->recordedEvents[] = new EmployeeEnrolled($id->value);

        return $e;
    }

    public static function reconstitute(TrainingEnrollmentId $id, string $sessionId, string $employeeId, \DateTimeImmutable $enrolledAt, ?array $attendance, EnrollmentStatus $status): self
    {
        return new self($id, $sessionId, $employeeId, $enrolledAt, $attendance, $status);
    }

    public function recordAttendance(array $attendance): void
    {
        if ($this->status !== EnrollmentStatus::Enrolled && $this->status !== EnrollmentStatus::Completed) {
            throw new InvalidEnrollmentStatusException($this->status->value, 'record attendance');
        }
        $this->attendance = $attendance;
        $this->recordedEvents[] = new AttendanceRecorded($this->id->value);
    }

    public function complete(): void
    {
        if (! $this->status->canTransitionTo(EnrollmentStatus::Completed)) {
            throw new InvalidEnrollmentStatusException($this->status->value, EnrollmentStatus::Completed->value);
        }
        $this->status = EnrollmentStatus::Completed;
    }

    public function cancel(): void
    {
        if (! $this->status->canTransitionTo(EnrollmentStatus::Cancelled)) {
            throw new InvalidEnrollmentStatusException($this->status->value, EnrollmentStatus::Cancelled->value);
        }
        $this->status = EnrollmentStatus::Cancelled;
        $this->recordedEvents[] = new EnrollmentCancelled($this->id->value);
    }

    public function getId(): TrainingEnrollmentId
    {
        return $this->id;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function getEmployeeId(): string
    {
        return $this->employeeId;
    }

    public function getEnrolledAt(): \DateTimeImmutable
    {
        return $this->enrolledAt;
    }

    public function getAttendance(): ?array
    {
        return $this->attendance;
    }

    public function getStatus(): EnrollmentStatus
    {
        return $this->status;
    }

    public function popRecordedEvents(): array
    {
        $e = $this->recordedEvents;
        $this->recordedEvents = [];

        return $e;
    }
}
