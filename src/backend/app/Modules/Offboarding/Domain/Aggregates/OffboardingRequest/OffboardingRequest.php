<?php

namespace App\Modules\Offboarding\Domain\Aggregates\OffboardingRequest;

use App\Modules\Offboarding\Domain\Events\OffboardingRequestApproved;
use App\Modules\Offboarding\Domain\Events\OffboardingRequestCreated;
use App\Modules\Offboarding\Domain\Events\OffboardingRequestRejected;
use App\Modules\Offboarding\Domain\Events\OffboardingRequestSubmitted;
use App\Modules\Offboarding\Domain\Exceptions\InvalidStatusTransitionException;
use App\Modules\Offboarding\Domain\ValueObjects\OffboardingRequestStatus;
use App\Modules\Offboarding\Domain\ValueObjects\OffboardingRequestType;

class OffboardingRequest
{
    private array $recordedEvents = [];

    private function __construct(
        private readonly OffboardingRequestId $id,
        private readonly string $employeeId,
        private readonly OffboardingRequestType $type,
        private string $reason,
        private readonly \DateTimeImmutable $requestedLastWorkingDate,
        private ?\DateTimeImmutable $approvedLastWorkingDate,
        private OffboardingRequestStatus $status,
        private ?string $workflowRequestId,
    ) {}

    public static function create(OffboardingRequestId $id, string $employeeId, OffboardingRequestType $type, string $reason, \DateTimeImmutable $requestedLastWorkingDate): self
    {
        $request = new self($id, $employeeId, $type, $reason, $requestedLastWorkingDate, null, OffboardingRequestStatus::Draft, null);
        $request->recordEvent(new OffboardingRequestCreated($id, $employeeId));

        return $request;
    }

    public static function reconstitute(OffboardingRequestId $id, string $employeeId, OffboardingRequestType $type, string $reason, \DateTimeImmutable $requestedLastWorkingDate, ?\DateTimeImmutable $approvedLastWorkingDate, OffboardingRequestStatus $status, ?string $workflowRequestId): self
    {
        return new self($id, $employeeId, $type, $reason, $requestedLastWorkingDate, $approvedLastWorkingDate, $status, $workflowRequestId);
    }

    public function submit(?string $workflowRequestId = null): void
    {
        $this->transition(OffboardingRequestStatus::PendingApproval);
        $this->workflowRequestId = $workflowRequestId;
        $this->recordEvent(new OffboardingRequestSubmitted($this->id));
    }

    public function approve(\DateTimeImmutable $approvedLastWorkingDate): void
    {
        $this->transition(OffboardingRequestStatus::Approved);
        $this->approvedLastWorkingDate = $approvedLastWorkingDate;
        $this->recordEvent(new OffboardingRequestApproved($this->id, $approvedLastWorkingDate));
    }

    public function reject(?string $reason = null): void
    {
        $this->transition(OffboardingRequestStatus::Rejected);
        if ($reason) {
            $this->reason = $reason;
        }
        $this->recordEvent(new OffboardingRequestRejected($this->id, $reason));
    }

    public function cancel(): void
    {
        $this->transition(OffboardingRequestStatus::Cancelled);
    }

    private function transition(OffboardingRequestStatus $target): void
    {
        if (! $this->status->canTransitionTo($target)) {
            throw new InvalidStatusTransitionException($this->status->value, $target->value);
        }
        $this->status = $target;
    }

    private function recordEvent(object $event): void
    {
        $this->recordedEvents[] = $event;
    }

    public function popRecordedEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];

        return $events;
    }

    public function getId(): OffboardingRequestId
    {
        return $this->id;
    }

    public function getEmployeeId(): string
    {
        return $this->employeeId;
    }

    public function getType(): OffboardingRequestType
    {
        return $this->type;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getRequestedLastWorkingDate(): \DateTimeImmutable
    {
        return $this->requestedLastWorkingDate;
    }

    public function getApprovedLastWorkingDate(): ?\DateTimeImmutable
    {
        return $this->approvedLastWorkingDate;
    }

    public function getStatus(): OffboardingRequestStatus
    {
        return $this->status;
    }

    public function getWorkflowRequestId(): ?string
    {
        return $this->workflowRequestId;
    }
}
