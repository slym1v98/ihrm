<?php

namespace App\Modules\Onboarding\Infrastructure\Persistence\Repositories;

use App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTask;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTaskId;
use App\Modules\Onboarding\Domain\Repositories\OnboardingTaskRepositoryInterface;
use App\Modules\Onboarding\Domain\ValueObjects\OnboardingTaskStatus;
use App\Modules\Onboarding\Domain\ValueObjects\OwnerType;
use App\Modules\Onboarding\Domain\ValueObjects\TaskType;
use App\Modules\Onboarding\Infrastructure\Persistence\Eloquent\OnboardingTaskModel;

class EloquentOnboardingTaskRepository implements OnboardingTaskRepositoryInterface
{
    public function findById(OnboardingTaskId $id): ?OnboardingTask
    {
        $model = OnboardingTaskModel::find($id->value);

        return $model ? $this->toDomain($model) : null;
    }

    public function findByPlanId(string $planId): array
    {
        return OnboardingTaskModel::where('onboarding_plan_id', $planId)
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($m) => $this->toDomain($m))
            ->toArray();
    }

    public function findByOwner(string $ownerType, string $ownerId): array
    {
        return OnboardingTaskModel::where('owner_type', $ownerType)
            ->where('owner_id', $ownerId)
            ->get()
            ->map(fn ($m) => $this->toDomain($m))
            ->toArray();
    }

    public function findByApprovalWorkflowRequestId(string $requestId): ?OnboardingTask
    {
        $model = OnboardingTaskModel::where('approval_workflow_request_id', $requestId)->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function save(OnboardingTask $task): void
    {
        OnboardingTaskModel::updateOrCreate(
            ['id' => $task->getId()->value],
            [
                'onboarding_plan_id' => $task->getPlanId(),
                'task_type' => $task->getTaskType()->value,
                'owner_type' => $task->getOwnerType()->value,
                'owner_id' => $task->getOwnerId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'due_date' => $task->getDueDate()?->format('Y-m-d'),
                'status' => $task->getStatus()->value,
                'requires_approval' => $task->isRequiresApproval(),
                'approval_workflow_request_id' => $task->getApprovalWorkflowRequestId(),
                'proof_file_object_id' => $task->getProofFileObjectId(),
                'sort_order' => $task->getSortOrder(),
                'is_pre_start' => $task->isPreStart(),
            ]
        );
    }

    public function delete(OnboardingTaskId $id): void
    {
        OnboardingTaskModel::destroy($id->value);
    }

    private function toDomain(OnboardingTaskModel $model): OnboardingTask
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
