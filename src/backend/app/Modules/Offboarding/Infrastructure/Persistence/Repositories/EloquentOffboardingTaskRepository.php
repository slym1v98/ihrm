<?php

namespace App\Modules\Offboarding\Infrastructure\Persistence\Repositories;

use App\Modules\Offboarding\Domain\Aggregates\OffboardingTask\OffboardingTask;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingTask\OffboardingTaskId;
use App\Modules\Offboarding\Domain\Repositories\OffboardingTaskRepositoryInterface;
use App\Modules\Offboarding\Domain\ValueObjects\OffboardingTaskStatus;
use App\Modules\Offboarding\Domain\ValueObjects\OwnerType;
use App\Modules\Offboarding\Domain\ValueObjects\TaskType;
use App\Modules\Offboarding\Infrastructure\Persistence\Eloquent\OffboardingTaskModel;

class EloquentOffboardingTaskRepository implements OffboardingTaskRepositoryInterface
{
    public function findById(OffboardingTaskId $id): ?OffboardingTask
    {
        $model = OffboardingTaskModel::find($id->value);
        return $model ? $this->toDomain($model) : null;
    }

    public function findByPlanId(string $planId): array
    {
        return OffboardingTaskModel::where('offboarding_plan_id', $planId)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($m) => $this->toDomain($m))
            ->toArray();
    }

    public function findByOwner(string $ownerType, string $ownerId): array
    {
        return OffboardingTaskModel::where('owner_type', $ownerType)
            ->where('owner_id', $ownerId)
            ->get()
            ->map(fn($m) => $this->toDomain($m))
            ->toArray();
    }

    public function findByApprovalWorkflowRequestId(string $requestId): ?OffboardingTask
    {
        $model = OffboardingTaskModel::where('approval_workflow_request_id', $requestId)->first();
        return $model ? $this->toDomain($model) : null;
    }

    public function save(OffboardingTask $task): void
    {
        OffboardingTaskModel::updateOrCreate(
            ['id' => $task->getId()->value],
            [
                'offboarding_plan_id' => $task->getPlanId(),
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

    public function delete(OffboardingTaskId $id): void
    {
        OffboardingTaskModel::destroy($id->value);
    }

    private function toDomain(OffboardingTaskModel $model): OffboardingTask
    {
        return OffboardingTask::reconstitute(
            OffboardingTaskId::fromString($model->id),
            $model->offboarding_plan_id,
            TaskType::from($model->task_type),
            OwnerType::from($model->owner_type),
            $model->owner_id,
            $model->title,
            $model->description,
            $model->due_date ? new \DateTimeImmutable($model->due_date) : null,
            OffboardingTaskStatus::from($model->status),
            $model->requires_approval,
            $model->approval_workflow_request_id,
            $model->proof_file_object_id,
            $model->is_pre_start,
            $model->sort_order,
        );
    }
}
