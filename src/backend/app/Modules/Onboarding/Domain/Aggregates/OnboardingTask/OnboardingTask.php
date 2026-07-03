<?php

namespace App\Modules\Onboarding\Domain\Aggregates\OnboardingTask;

use App\Modules\Onboarding\Domain\Events\OnboardingTaskCompleted;
use App\Modules\Onboarding\Domain\Events\OnboardingTaskStarted;
use App\Modules\Onboarding\Domain\Events\OnboardingTaskWaived;
use App\Modules\Onboarding\Domain\Exceptions\InvalidStatusTransitionException;
use App\Modules\Onboarding\Domain\ValueObjects\OnboardingTaskStatus;
use App\Modules\Onboarding\Domain\ValueObjects\OwnerType;
use App\Modules\Onboarding\Domain\ValueObjects\TaskType;

class OnboardingTask
{
    /** @var object[] */
    private array $recordedEvents = [];

    private function __construct(
        private readonly OnboardingTaskId $id,
        private readonly string $planId,
        private readonly TaskType $taskType,
        private readonly OwnerType $ownerType,
        private readonly string $ownerId,
        private string $title,
        private ?string $description,
        private ?\DateTimeImmutable $dueDate,
        private OnboardingTaskStatus $status,
        private bool $requiresApproval,
        private ?string $approvalWorkflowRequestId,
        private ?string $proofFileObjectId,
        private bool $isPreStart,
        private int $sortOrder,
    ) {}

    public static function create(
        OnboardingTaskId $id,
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
            $dueDate, OnboardingTaskStatus::Pending, $requiresApproval, null, null, $isPreStart, $sortOrder
        );
    }

    public static function reconstitute(
        OnboardingTaskId $id,
        string $planId,
        TaskType $taskType,
        OwnerType $ownerType,
        string $ownerId,
        string $title,
        ?string $description,
        ?\DateTimeImmutable $dueDate,
        OnboardingTaskStatus $status,
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
        if (!$this->status->canTransitionTo(OnboardingTaskStatus::InProgress)) {
            throw new InvalidStatusTransitionException($this->status->value, OnboardingTaskStatus::InProgress->value);
        }
        $this->status = OnboardingTaskStatus::InProgress;
        $this->recordEvent(new OnboardingTaskStarted($this->id, $this->planId));
    }

    public function complete(?string $proofFileObjectId = null): void
    {
        if (!$this->status->canTransitionTo(OnboardingTaskStatus::Completed)) {
            throw new InvalidStatusTransitionException($this->status->value, OnboardingTaskStatus::Completed->value);
        }

        if ($this->requiresApproval) {
            $this->proofFileObjectId = $proofFileObjectId;
            return;
        }

        $this->proofFileObjectId = $proofFileObjectId;
        $this->status = OnboardingTaskStatus::Completed;
        $this->recordEvent(new OnboardingTaskCompleted($this->id, $this->planId, $proofFileObjectId));
    }

    public function waive(?string $reason = null): void
    {
        if (!$this->status->canTransitionTo(OnboardingTaskStatus::Waived)) {
            throw new InvalidStatusTransitionException($this->status->value, OnboardingTaskStatus::Waived->value);
        }
        $this->status = OnboardingTaskStatus::Waived;
        $this->recordEvent(new OnboardingTaskWaived($this->id, $this->planId, $reason));
    }

    public function markApproved(): void
    {
        if ($this->status !== OnboardingTaskStatus::InProgress) {
            throw new InvalidStatusTransitionException($this->status->value, OnboardingTaskStatus::Completed->value);
        }
        if (!$this->requiresApproval) {
            throw new \RuntimeException('Task does not require approval');
        }
        $this->status = OnboardingTaskStatus::Completed;
        $this->recordEvent(new OnboardingTaskCompleted($this->id, $this->planId, $this->proofFileObjectId));
    }

    public function setApprovalWorkflowRequestId(string $id): void { $this->approvalWorkflowRequestId = $id; }

    public function recordEvent(object $event): void { $this->recordedEvents[] = $event; }
    public function popRecordedEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];
        return $events;
    }

    public function getId(): OnboardingTaskId { return $this->id; }
    public function getPlanId(): string { return $this->planId; }
    public function getTaskType(): TaskType { return $this->taskType; }
    public function getOwnerType(): OwnerType { return $this->ownerType; }
    public function getOwnerId(): string { return $this->ownerId; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): ?string { return $this->description; }
    public function getDueDate(): ?\DateTimeImmutable { return $this->dueDate; }
    public function getStatus(): OnboardingTaskStatus { return $this->status; }
    public function isRequiresApproval(): bool { return $this->requiresApproval; }
    public function getApprovalWorkflowRequestId(): ?string { return $this->approvalWorkflowRequestId; }
    public function getProofFileObjectId(): ?string { return $this->proofFileObjectId; }
    public function getSortOrder(): int { return $this->sortOrder; }
    public function isPreStart(): bool { return $this->isPreStart; }
}
