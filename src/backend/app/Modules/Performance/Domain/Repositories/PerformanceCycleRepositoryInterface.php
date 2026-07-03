<?php

namespace App\Modules\Performance\Domain\Repositories;

use App\Modules\Performance\Domain\Aggregates\PerformanceCycle\PerformanceCycle;
use App\Modules\Performance\Domain\Aggregates\PerformanceCycle\PerformanceCycleId;

interface PerformanceCycleRepositoryInterface
{
    public function findById(PerformanceCycleId $id): ?PerformanceCycle;
    public function findByCode(string $code): ?PerformanceCycle;
    public function all(): array;
    public function save(PerformanceCycle $cycle): void;
}
