<?php

namespace App\Modules\Onboarding\Infrastructure\Persistence\Repositories;

use App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlan;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlanId;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTask;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTaskId;
use App\Modules\Onboarding\Domain\Repositories\OnboardingPlanRepositoryInterface;
use App\Modules\Onboarding\Domain\Repositories\OnboardingTaskRepositoryInterface;
use App\Modules\Onboarding\Domain\ValueObjects\OnboardingPlanStatus;
use App\Modules\Onboarding\Domain\ValueObjects\OnboardingTaskStatus;
use App\Modules\Onboarding\Domain\ValueObjects\OwnerType;
use App\Modules\Onboarding\Domain\ValueObjects\TaskType;
use App\Modules\Onboarding\Infrastructure\Persistence\Eloquent\OnboardingPlanModel;
use App\Modules\Onboarding\Infrastructure\Persistence\Eloquent\OnboardingTaskModel;

class EloquentOnboardingPlanRepository implements OnboardingPlanRepositoryInterface
{
    public function findById(OnboardingPlanId $id): ?OnboardingPlan
    {
        $model = OnboardingPlanModel::with('tasks')->find($id->value);
        return $model ? $this->toDomain($model) : null;
    }

    public function findByEmployeeId(string $employeeId): array
    {
        return OnboardingPlanModel::with('tasks')
            ->where('employee_id', $employeeId)
            ->get()
            ->map(fn($m) => $this->toDomain($m))
            ->toArray();
    }

    public function findByWorkflowRequestId(string $workflowRequestId): ?OnboardingPlan
    {
        $model = OnboardingPlanModel::with('tasks')
            ->where('workflow_request_id', $workflowRequestId)
            ->first();
        return $model ? $this->toDomain($model) : null;
    }

    public function all(): array
    {
        return OnboardingPlanModel::with('tasks')
            ->get()
            ->map(fn($m) => $this->toDomain($m))
            ->toArray();
    }

    public function save(OnboardingPlan $plan): void
    {
        OnboardingPlanModel::updateOrCreate(
            ['id' => $plan->getId()->value],
            [
                'employee_id' => $plan->getEmployeeId(),
                'candidate_id' => $plan->getCandidateId(),
                'template_id' => $plan->getTemplateId(),
                'start_date' => $plan->getStartDate()->format('Y-m-d'),
                'status' => $plan->getStatus()->value,
                'workflow_request_id' => $plan->getWorkflowRequestId(),
                'completed_at' => $plan->getCompletedAt()?->format('Y-m-d H:i:s'),
            ]
        );

        foreach ($plan->getTasks() as $task) {
            $taskRepo = app(OnboardingTaskRepositoryInterface::class);
            $taskRepo->save($task);
        }
    }

    public function delete(OnboardingPlanId $id): void
    {
        OnboardingPlanModel::destroy($id->value);
    }

    private function toDomain(OnboardingPlanModel $model): OnboardingPlan
    {
        $plan = OnboardingPlan::reconstitute(
            OnboardingPlanId::fromString($model->id),
            $model->employee_id,
            $model->candidate_id,
            $model->template_id,
            new \DateTimeImmutable($model->start_date),
            OnboardingPlanStatus::from($model->status),
            $model->workflow_request_id,
            $model->completed_at ? new \DateTimeImmutable($model->completed_at) : null,
        );

        foreach ($model->tasks ?? [] as $taskModel) {
            $plan->addGeneratedTask($this->taskToDomain($taskModel));
        }

        return $plan;
    }

    private function taskToDomain(OnboardingTaskModel $model): OnboardingTask
    {
        return OnboardingTask::reconstitute(
            OnboardingTaskId::fromString($model->id),
            $model->onboarding_plan_id,
            TaskType::from($model->task_type),
            OwnerType::from($model->owner_type),
            $model->owner_id,
            $model->title,
            $model->description,
            $model->due_date ? new \DateTimeImmutable($model->due_date) : null,
            OnboardingTaskStatus::from($model->status),
            $model->requires_approval,
            $model->approval_workflow_request_id,
            $model->proof_file_object_id,
            $model->is_pre_start,
            $model->sort_order,
        );
    }
}
