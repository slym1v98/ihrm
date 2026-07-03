<?php

namespace App\Modules\Offboarding\Domain\Aggregates\OffboardingTask;

use App\Modules\Offboarding\Domain\Events\OffboardingTaskCompleted;
use App\Modules\Offboarding\Domain\Events\OffboardingTaskStarted;
use App\Modules\Offboarding\Domain\Events\OffboardingTaskWaived;
use App\Modules\Offboarding\Domain\Exceptions\InvalidStatusTransitionException;
use App\Modules\Offboarding\Domain\ValueObjects\OffboardingTaskStatus;
use App\Modules\Offboarding\Domain\ValueObjects\OwnerType;
use App\Modules\Offboarding\Domain\ValueObjects\TaskType;

class OffboardingTask
{
    /** @var object[] */
    private array $recordedEvents = [];

    private function __construct(
        private readonly OffboardingTaskId $id,
        private readonly string $planId,
        private readonly TaskType $taskType,
        private readonly OwnerType $ownerType,
        private readonly string $ownerId,
        private string $title,
        private ?string $description,
        private ?\DateTimeImmutable $dueDate,
        private OffboardingTaskStatus $status,
        private bool $requiresApproval,
        private ?string $approvalWorkflowRequestId,
        private ?string $proofFileObjectId,
        private bool $isPreStart,
        private int $sortOrder,
    ) {}

    public static function create(
        OffboardingTaskId $id,
        string $planId,
        TaskType $taskType,
        OwnerType $ownerType,
        string $ownerId,
        string $title,
        ?string $description,
        ?\DateTimeImmutable $dueDate,
        bool $requiresApproval,
        bool $isPreStart,
        int $sortOrder,
    ): self {
        return new self(
            $id, $planId, $taskType, $ownerType, $ownerId, $title, $description,
            $dueDate, OffboardingTaskStatus::Pending, $requiresApproval, null, null, $isPreStart, $sortOrder
        );
    }

    public static function reconstitute(
        OffboardingTaskId $id,
        string $planId,
        TaskType $taskType,
        OwnerType $ownerType,
        string $ownerId,
        string $title,
        ?string $description,
        ?\DateTimeImmutable $dueDate,
        OffboardingTaskStatus $status,
        bool $requiresApproval,
        ?string $approvalWorkflowRequestId,
        ?string $proofFileObjectId,
        bool $isPreStart,
        int $sortOrder,
    ): self {
        return new self(
            $id, $planId, $taskType, $ownerType, $ownerId, $title, $description,
            $dueDate, $status, $requiresApproval, $approvalWorkflowRequestId,
            $proofFileObjectId, $isPreStart, $sortOrder
        );
    }

    public function update(string $title, ?string $description): void
    {
        if ($this->status->isTerminal()) {
            throw new \RuntimeException('Cannot update a completed or waived task');
        }
        $this->title = $title;
        $this->description = $description;
    }

    public function start(): void
    {
        if (!$this->status->canTransitionTo(OffboardingTaskStatus::InProgress)) {
            throw new InvalidStatusTransitionException($this->status->value, OffboardingTaskStatus::InProgress->value);
        }
        $this->status = OffboardingTaskStatus::InProgress;
        $this->recordEvent(new OffboardingTaskStarted($this->id, $this->planId));
    }

    public function complete(?string $proofFileObjectId = null): void
    {
        if (!$this->status->canTransitionTo(OffboardingTaskStatus::Completed)) {
            throw new InvalidStatusTransitionException($this->status->value, OffboardingTaskStatus::Completed->value);
        }

        if ($this->requiresApproval) {
            $this->proofFileObjectId = $proofFileObjectId;
            return;
        }

        $this->proofFileObjectId = $proofFileObjectId;
        $this->status = OffboardingTaskStatus::Completed;
        $this->recordEvent(new OffboardingTaskCompleted($this->id, $this->planId, $proofFileObjectId));
    }

    public function waive(?string $reason = null): void
    {
        if (!$this->status->canTransitionTo(OffboardingTaskStatus::Waived)) {
            throw new InvalidStatusTransitionException($this->status->value, OffboardingTaskStatus::Waived->value);
        }
        $this->status = OffboardingTaskStatus::Waived;
        $this->recordEvent(new OffboardingTaskWaived($this->id, $this->planId, $reason));
    }

    public function markApproved(): void
    {
        if ($this->status !== OffboardingTaskStatus::InProgress) {
            throw new InvalidStatusTransitionException($this->status->value, OffboardingTaskStatus::Completed->value);
        }
        if (!$this->requiresApproval) {
            throw new \RuntimeException('Task does not require approval');
        }
        $this->status = OffboardingTaskStatus::Completed;
        $this->recordEvent(new OffboardingTaskCompleted($this->id, $this->planId, $this->proofFileObjectId));
    }

    public function setApprovalWorkflowRequestId(string $id): void { $this->approvalWorkflowRequestId = $id; }

    public function recordEvent(object $event): void { $this->recordedEvents[] = $event; }
    public function popRecordedEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];
        return $events;
    }

    public function getId(): OffboardingTaskId { return $this->id; }
    public function getPlanId(): string { return $this->planId; }
    public function getTaskType(): TaskType { return $this->taskType; }
    public function getOwnerType(): OwnerType { return $this->ownerType; }
    public function getOwnerId(): string { return $this->ownerId; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): ?string { return $this->description; }
    public function getDueDate(): ?\DateTimeImmutable { return $this->dueDate; }
    public function getStatus(): OffboardingTaskStatus { return $this->status; }
    public function isRequiresApproval(): bool { return $this->requiresApproval; }
    public function getApprovalWorkflowRequestId(): ?string { return $this->approvalWorkflowRequestId; }
    public function getProofFileObjectId(): ?string { return $this->proofFileObjectId; }
    public function getSortOrder(): int { return $this->sortOrder; }
    public function isPreStart(): bool { return $this->isPreStart; }
}
