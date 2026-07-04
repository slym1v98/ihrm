<?php

namespace App\Modules\Workflow\Infrastructure\Persistence\Repositories;

use App\Modules\Workflow\Domain\Aggregates\WorkflowDelegation\WorkflowDelegation;
use App\Modules\Workflow\Domain\Aggregates\WorkflowDelegation\WorkflowDelegationId;
use App\Modules\Workflow\Domain\Repositories\WorkflowDelegationRepositoryInterface;
use App\Modules\Workflow\Infrastructure\Persistence\Eloquent\WorkflowDelegationModel;
use Carbon\CarbonImmutable;

class EloquentWorkflowDelegationRepository implements WorkflowDelegationRepositoryInterface
{
    public function findById(WorkflowDelegationId $id): ?WorkflowDelegation
    {
        $model = WorkflowDelegationModel::find($id->value());

        return $model ? $this->toDomain($model) : null;
    }

    public function findActiveForDelegator(string $delegatorId, CarbonImmutable $at, ?string $roleType = null): array
    {
        $query = WorkflowDelegationModel::query()
            ->where('delegator_id', $delegatorId)
            ->where('active', true)
            ->where('start_at', '<=', $at)
            ->where('end_at', '>=', $at);

        if ($roleType !== null) {
            $query->where('role_type', $roleType);
        }

        return $query->orderBy('start_at')->get()->map(fn (WorkflowDelegationModel $model) => $this->toDomain($model))->all();
    }

    public function hasOverlap(string $delegatorId, CarbonImmutable $startAt, CarbonImmutable $endAt, ?string $roleType = null, ?string $ignoreId = null): bool
    {
        $query = WorkflowDelegationModel::query()
            ->where('delegator_id', $delegatorId)
            ->where('active', true)
            ->where(function ($query) use ($startAt, $endAt) {
                $query->whereBetween('start_at', [$startAt, $endAt])
                    ->orWhereBetween('end_at', [$startAt, $endAt])
                    ->orWhere(function ($query) use ($startAt, $endAt) {
                        $query->where('start_at', '<=', $startAt)
                            ->where('end_at', '>=', $endAt);
                    });
            });

        if ($roleType === null) {
            $query->whereNull('role_type');
        } else {
            $query->where('role_type', $roleType);
        }

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    public function save(WorkflowDelegation $delegation): void
    {
        WorkflowDelegationModel::updateOrCreate(
            ['id' => $delegation->id()->value()],
            [
                'delegator_id' => $delegation->delegatorId(),
                'delegate_id' => $delegation->delegateId(),
                'role_type' => $delegation->roleType(),
                'start_at' => $delegation->startAt(),
                'end_at' => $delegation->endAt(),
                'active' => $delegation->active(),
                'created_by' => $delegation->createdBy(),
            ],
        );
    }

    private function toDomain(WorkflowDelegationModel $model): WorkflowDelegation
    {
        return new WorkflowDelegation(
            new WorkflowDelegationId($model->id),
            $model->delegator_id,
            $model->delegate_id,
            $model->role_type,
            CarbonImmutable::parse($model->start_at),
            CarbonImmutable::parse($model->end_at),
            $model->active,
            $model->created_by,
        );
    }
}
