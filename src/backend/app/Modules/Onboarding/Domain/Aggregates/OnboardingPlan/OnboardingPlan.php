<?php

namespace App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan;

use App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTask;
use App\Modules\Onboarding\Domain\Events\OnboardingPlanActivated;
use App\Modules\Onboarding\Domain\Events\OnboardingPlanCompleted;
use App\Modules\Onboarding\Domain\Events\OnboardingPlanCreated;
use App\Modules\Onboarding\Domain\Events\OnboardingTaskAssigned;
use App\Modules\Onboarding\Domain\Exceptions\InvalidStatusTransitionException;
use App\Modules\Onboarding\Domain\Exceptions\MandatoryTaskIncompleteException;
use App\Modules\Onboarding\Domain\ValueObjects\OnboardingPlanStatus;
use App\Modules\Onboarding\Domain\ValueObjects\TaskType;

class OnboardingPlan
{
    /** @var OnboardingTask[] */
    private array $tasks = [];

    /** @var object[] */
    private array $recordedEvents = [];

    private function __construct(
        private readonly OnboardingPlanId $id,
        private readonly string $employeeId,
        private readonly ?string $candidateId,
        private readonly ?string $templateId,
        private readonly \DateTimeImmutable $startDate,
        private OnboardingPlanStatus $status,
        private ?string $workflowRequestId,
        private ?\DateTimeImmutable $completedAt,
    ) {}

    public static function create(
        OnboardingPlanId $id,
        string $employeeId,
        ?string $candidateId,
        ?string $templateId,
        \DateTimeImmutable $startDate,
    ): self {
        $plan = new self($id, $employeeId, $candidateId, $templateId, $startDate, OnboardingPlanStatus::Draft, null, null);
        $plan->recordEvent(new OnboardingPlanCreated($id, $employeeId, $startDate));
        return $plan;
    }

    public static function reconstitute(
        OnboardingPlanId $id,
        string $employeeId,
        ?string $candidateId,
        ?string $templateId,
        \DateTimeImmutable $startDate,
        OnboardingPlanStatus $status,
        ?string $workflowRequestId,
        ?\DateTimeImmutable $completedAt,
    ): self {
        return new self($id, $employeeId, $candidateId, $templateId, $startDate, $status, $workflowRequestId, $completedAt);
    }

    public function activate(): void
    {
        if (!$this->status->canTransitionTo(OnboardingPlanStatus::Active)) {
            throw new InvalidStatusTransitionException($this->status->value, OnboardingPlanStatus::Active->value);
        }
        if (count($this->tasks) === 0) {
            throw new \RuntimeException('Plan must have at least one task to activate');
        }
        $this->status = OnboardingPlanStatus::Active;
        $this->recordEvent(new OnboardingPlanActivated($this->id, $this->employeeId, $this->startDate));
    }

    public function cancel(): void
    {
        if (!$this->status->canTransitionTo(OnboardingPlanStatus::Cancelled)) {
            throw new InvalidStatusTransitionException($this->status->value, OnboardingPlanStatus::Cancelled->value);
        }
        $this->status = OnboardingPlanStatus::Cancelled;
    }

    public function complete(): void
    {
        if (!$this->status->canTransitionTo(OnboardingPlanStatus::Completed)) {
            throw new InvalidStatusTransitionException($this->status->value, OnboardingPlanStatus::Completed->value);
        }

        $pendingTasks = array_filter(
            $this->tasks,
            fn(OnboardingTask $t) => !$t->getStatus()->isTerminal()
        );

        if (!empty($pendingTasks)) {
            throw new MandatoryTaskIncompleteException();
        }

        if ($this->workflowRequestId !== null) {
            return;
        }

        $this->status = OnboardingPlanStatus::Completed;
        $this->completedAt = new \DateTimeImmutable();
        $this->recordEvent(new OnboardingPlanCompleted($this->id, $this->employeeId));
    }

    public function markWorkflowApproved(): void
    {
        if ($this->status !== OnboardingPlanStatus::Active) {
            throw new InvalidStatusTransitionException($this->status->value, OnboardingPlanStatus::Completed->value);
        }
        $this->status = OnboardingPlanStatus::Completed;
        $this->completedAt = new \DateTimeImmutable();
        $this->recordEvent(new OnboardingPlanCompleted($this->id, $this->employeeId));
    }

    public function addTask(OnboardingTask $task): void
    {
        if ($this->status !== OnboardingPlanStatus::Active) {
            throw new \RuntimeException('Can only add tasks to active plans');
        }
        if ($task->getTaskType() !== TaskType::Custom) {
            throw new \RuntimeException('Only custom tasks can be added manually');
        }
        if (count($this->tasks) >= 50) {
            throw new \RuntimeException('Maximum 50 custom tasks per plan');
        }
        $this->tasks[] = $task;
        $this->recordEvent(new OnboardingTaskAssigned(
            $task->getId(), $this->id, $task->getOwnerType()->value, $task->getOwnerId(), $task->getDueDate()
        ));
    }

    public function addGeneratedTask(OnboardingTask $task): void
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

    public function getId(): OnboardingPlanId { return $this->id; }
    public function getEmployeeId(): string { return $this->employeeId; }
    public function getCandidateId(): ?string { return $this->candidateId; }
    public function getTemplateId(): ?string { return $this->templateId; }
    public function getStartDate(): \DateTimeImmutable { return $this->startDate; }
    public function getStatus(): OnboardingPlanStatus { return $this->status; }
    public function getWorkflowRequestId(): ?string { return $this->workflowRequestId; }
    public function getCompletedAt(): ?\DateTimeImmutable { return $this->completedAt; }
    public function getTasks(): array { return $this->tasks; }
}
