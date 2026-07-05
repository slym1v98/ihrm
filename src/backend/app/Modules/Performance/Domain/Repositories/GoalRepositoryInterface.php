<?php

namespace App\Modules\Performance\Domain\Repositories;

use App\Modules\Performance\Domain\Aggregates\Goal\Goal;
use App\Modules\Performance\Domain\Aggregates\Goal\GoalId;

interface GoalRepositoryInterface
{
    public function findById(GoalId $id): ?Goal;

    public function findByCycleId(string $cycleId): array;

    public function findByEmployeeId(string $employeeId): array;

    public function save(Goal $goal): void;
}
