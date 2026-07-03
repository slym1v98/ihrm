<?php

namespace App\Modules\Offboarding\Domain\Aggregates\OffboardingPlan;

use App\Modules\Offboarding\Domain\Aggregates\OffboardingTask\OffboardingTask;
use App\Modules\Offboarding\Domain\Events\OffboardingPlanActivated;
use App\Modules\Offboarding\Domain\Events\OffboardingPlanCompleted;
use App\Modules\Offboarding\Domain\Events\OffboardingPlanCreated;
use App\Modules\Offboarding\Domain\Events\OffboardingTaskAssigned;
use App\Modules\Offboarding\Domain\Exceptions\InvalidStatusTransitionException;
use App\Modules\Offboarding\Domain\Exceptions\MandatoryTaskIncompleteException;
use App\Modules\Offboarding\Domain\ValueObjects\OffboardingPlanStatus;
use App\Modules\Offboarding\Domain\ValueObjects\TaskType;

class OffboardingPlan
{
    /** @var OffboardingTask[] */
    private array $tasks = [];

    /** @var object[] */
    private array $recordedEvents = [];

    private function __construct(
        private readonly OffboardingPlanId $id,
        private readonly string $requestId,
    private readonly string $employeeId,
        private readonly \DateTimeImmutable $startDate,
        private OffboardingPlanStatus $status,
        private ?string $workflowRequestId,
        private ?\DateTimeImmutable $completedAt,
    ) {}

    public static function create(
        OffboardingPlanId $id,
        string $requestId,
        ?string $dummy,
        ?string $dummy2,
        \DateTimeImmutable $startDate,
    ): self {
        $plan = new self($id, $requestId, $dummy, $dummy2, $startDate, OffboardingPlanStatus::Draft, null, null);
        $plan->recordEvent(new OffboardingPlanCreated($id, $requestId, $startDate));
        return $plan;
    }

    public static function reconstitute(
        OffboardingPlanId $id,
        string $requestId,
        ?string $dummy,
        ?string $dummy2,
        \DateTimeImmutable $startDate,
        OffboardingPlanStatus $status,
        ?string $workflowRequestId,
        ?\DateTimeImmutable $completedAt,
    ): self {
        return new self($id, $requestId, $dummy, $dummy2, $startDate, $status, $workflowRequestId, $completedAt);
    }

    public function activate(): void
    {
        if (!$this->status->canTransitionTo(OffboardingPlanStatus::Active)) {
            throw new InvalidStatusTransitionException($this->status->value, OffboardingPlanStatus::Active->value);
        }
        if (count($this->tasks) === 0) {
            throw new \RuntimeException('Plan must have at least one task to activate');
        }
        $this->status = OffboardingPlanStatus::Active;
        $this->recordEvent(new OffboardingPlanActivated($this->id, $this->requestId, $this->startDate));
    }

    public function cancel(): void
    {
        if (!$this->status->canTransitionTo(OffboardingPlanStatus::Cancelled)) {
            throw new InvalidStatusTransitionException($this->status->value, OffboardingPlanStatus::Cancelled->value);
        }
        $this->status = OffboardingPlanStatus::Cancelled;
    }

    public function complete(): void
    {
        if (!$this->status->canTransitionTo(OffboardingPlanStatus::Completed)) {
            throw new InvalidStatusTransitionException($this->status->value, OffboardingPlanStatus::Completed->value);
        }

        $pendingTasks = array_filter(
            $this->tasks,
            fn(OffboardingTask $t) => !$t->getStatus()->isTerminal()
        );

        if (!empty($pendingTasks)) {
            throw new MandatoryTaskIncompleteException();
        }

        if ($this->workflowRequestId !== null) {
            return;
        }

        $this->status = OffboardingPlanStatus::Completed;
        $this->completedAt = new \DateTimeImmutable();
        $this->recordEvent(new OffboardingPlanCompleted($this->id, $this->requestId));
    }

    public function markWorkflowApproved(): void
    {
        if ($this->status !== OffboardingPlanStatus::Active) {
            throw new InvalidStatusTransitionException($this->status->value, OffboardingPlanStatus::Completed->value);
        }
        $this->status = OffboardingPlanStatus::Completed;
        $this->completedAt = new \DateTimeImmutable();
        $this->recordEvent(new OffboardingPlanCompleted($this->id, $this->requestId));
    }

    public function addTask(OffboardingTask $task): void
    {
        if (!in_array($this->status, [OffboardingPlanStatus::Draft, OffboardingPlanStatus::Active], true)) {
            throw new \RuntimeException('Can only add tasks to draft or active plans');
        }
        if ($task->getTaskType() !== TaskType::Custom) {
            throw new \RuntimeException('Only custom tasks can be added manually');
        }
        if (count($this->tasks) >= 50) {
            throw new \RuntimeException('Maximum 50 custom tasks per plan');
        }
        $this->tasks[] = $task;
        $this->recordEvent(new OffboardingTaskAssigned(
            $task->getId(), $this->id, $task->getOwnerType()->value, $task->getOwnerId(), $task->getDueDate()
        ));
    }

    public function addGeneratedTask(OffboardingTask $task): void
    {
        $this->tasks[] = $task;
    }

    public function removeTask(string $taskId): void
    {
        foreach ($this->tasks as $i => $task) {
            if ($task->getId()->value === $taskId) {
                if ($task->getTaskType() !== TaskType::Custom) {
                    throw new \RuntimeException('Cannot remove system-defined tasks');
                }
                unset($this->tasks[$i]);
                $this->tasks = array_values($this->tasks);
                return;
            }
        }
        throw new \RuntimeException("Task not found: {$taskId}");
    }

    public function setWorkflowRequestId(string $workflowRequestId): void
    {
        $this->workflowRequestId = $workflowRequestId;
    }

    public function recordEvent(object $event): void
    {
        $this->recordedEvents[] = $event;
    }

    public function popRecordedEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];
        return $events;
    }

    public function getId(): OffboardingPlanId { return $this->id; }
    public function getEmployeeId(): string { return $this->requestId; }
    public function getCandidateId(): ?string { return $this->dummy; }
    public function getTemplateId(): ?string { return $this->dummy2; }
    public function getStartDate(): \DateTimeImmutable { return $this->startDate; }
    public function getStatus(): OffboardingPlanStatus { return $this->status; }
    public function getWorkflowRequestId(): ?string { return $this->workflowRequestId; }
    public function getCompletedAt(): ?\DateTimeImmutable { return $this->completedAt; }
    public function getTasks(): array { return $this->tasks; }
}
