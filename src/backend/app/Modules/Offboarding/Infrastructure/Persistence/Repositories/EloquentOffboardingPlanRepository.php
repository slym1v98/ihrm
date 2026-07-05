<?php

namespace App\Modules\Offboarding\Infrastructure\Persistence\Repositories;

use App\Modules\Offboarding\Domain\Aggregates\OffboardingPlan\OffboardingPlan;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingPlan\OffboardingPlanId;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingTask\OffboardingTask;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingTask\OffboardingTaskId;
use App\Modules\Offboarding\Domain\Repositories\OffboardingPlanRepositoryInterface;
use App\Modules\Offboarding\Domain\Repositories\OffboardingTaskRepositoryInterface;
use App\Modules\Offboarding\Domain\ValueObjects\OffboardingPlanStatus;
use App\Modules\Offboarding\Domain\ValueObjects\OffboardingTaskStatus;
use App\Modules\Offboarding\Domain\ValueObjects\OwnerType;
use App\Modules\Offboarding\Domain\ValueObjects\TaskType;
use App\Modules\Offboarding\Infrastructure\Persistence\Eloquent\OffboardingPlanModel;
use App\Modules\Offboarding\Infrastructure\Persistence\Eloquent\OffboardingTaskModel;

class EloquentOffboardingPlanRepository implements OffboardingPlanRepositoryInterface
{
    public function findById(OffboardingPlanId $id): ?OffboardingPlan
    {
        $model = OffboardingPlanModel::with('tasks')->find($id->value);

        return $model ? $this->toDomain($model) : null;
    }

    public function findByRequestId(string $requestId): array
    {
        return OffboardingPlanModel::with('tasks')
            ->where('offboarding_request_id', $requestId)
            ->get()->map(fn ($m) => $this->toDomain($m))->toArray();
    }

    public function findByWorkflowRequestId(string $workflowRequestId): ?OffboardingPlan
    {
        $model = OffboardingPlanModel::with('tasks')
            ->where('id', 'like', '%')
            ->get()->first(function ($m) {
                return false; // Stub - would need a column
            });

        return $model ? $this->toDomain($model) : null;
    }

    public function all(): array
    {
        return OffboardingPlanModel::with('tasks')
            ->get()->map(fn ($m) => $this->toDomain($m))->toArray();
    }

    public function delete(OffboardingPlanId $id): void
    {
        OffboardingPlanModel::destroy($id->value);
    }

    public function findByEmployeeId(string $employeeId): array
    {
        return OffboardingPlanModel::where('offboarding_request_id', function ($q) use ($employeeId) {
            $q->select('id')->from('offboarding_requests')->where('employee_id', $employeeId);
        })->get()->map(fn ($m) => $this->toDomain($m))->toArray();
    }

    public function save(OffboardingPlan $plan): void
    {
        OffboardingPlanModel::updateOrCreate(
            ['id' => $plan->getId()->value],
            [
                'offboarding_request_id' => $plan->getRequestId(),
                'status' => $plan->getStatus()->value,
                'completed_at' => $plan->getCompletedAt()?->format('Y-m-d H:i:s'),
            ]
        );
        foreach ($plan->getTasks() as $task) {
            app(OffboardingTaskRepositoryInterface::class)->save($task);
        }
    }

    private function toDomain(OffboardingPlanModel $model): OffboardingPlan
    {
        $plan = OffboardingPlan::reconstitute(
            OffboardingPlanId::fromString($model->id),
            $model->offboarding_request_id,
            new \DateTimeImmutable('now'), // startDate — not stored on plans table
            OffboardingPlanStatus::from($model->status),
            null,
            $model->completed_at ? new \DateTimeImmutable($model->completed_at) : null,
        );
        foreach ($model->tasks ?? [] as $t) {
            $plan->addGeneratedTask($this->taskToDomain($t));
        }

        return $plan;
    }

    private function taskToDomain(OffboardingTaskModel $model): OffboardingTask
    {
        return OffboardingTask::reconstitute(
            OffboardingTaskId::fromString($model->id), $model->offboarding_plan_id,
            TaskType::from($model->task_type), OwnerType::from($model->owner_type), $model->owner_id,
            $model->title, $model->description,
            $model->due_date ? new \DateTimeImmutable($model->due_date) : null,
            OffboardingTaskStatus::from($model->status), $model->requires_approval,
            $model->approval_workflow_request_id, $model->proof_file_object_id, false, $model->sort_order ?? 0,
        );
    }
}
