<?php

namespace App\Modules\Performance\Infrastructure\Persistence\Repositories;

use App\Modules\Performance\Domain\Aggregates\Goal\Goal;
use App\Modules\Performance\Domain\Aggregates\Goal\GoalId;
use App\Modules\Performance\Domain\Repositories\GoalRepositoryInterface;
use App\Modules\Performance\Domain\ValueObjects\GoalStatus;
use App\Modules\Performance\Infrastructure\Persistence\Eloquent\GoalModel;

class EloquentGoalRepository implements GoalRepositoryInterface
{
    public function findById(GoalId $id): ?Goal
    {
        $m = GoalModel::find($id->value);

        return $m ? $this->toDomain($m) : null;
    }

    public function findByCycleId(string $cycleId): array
    {
        return GoalModel::where('cycle_id', $cycleId)->orderBy('sort_order')->get()->map(fn ($m) => $this->toDomain($m))->toArray();
    }

    public function findByEmployeeId(string $employeeId): array
    {
        return GoalModel::where('employee_id', $employeeId)->get()->map(fn ($m) => $this->toDomain($m))->toArray();
    }

    public function save(Goal $goal): void
    {
        GoalModel::updateOrCreate(['id' => $goal->getId()->value], [
            'cycle_id' => $goal->getCycleId(), 'employee_id' => $goal->getEmployeeId(), 'title' => $goal->getTitle(), 'description' => $goal->getDescription(),
            'weight' => $goal->getWeight(), 'target_value' => $goal->getTargetValue(), 'actual_value' => $goal->getActualValue(), 'status' => $goal->getStatus()->value, 'sort_order' => $goal->getSortOrder(),
        ]);
    }

    private function toDomain(GoalModel $m): Goal
    {
        return Goal::reconstitute(GoalId::fromString($m->id), $m->cycle_id, $m->employee_id, $m->title, $m->description, (float) $m->weight, $m->target_value, $m->actual_value, GoalStatus::from($m->status), $m->sort_order);
    }
}
