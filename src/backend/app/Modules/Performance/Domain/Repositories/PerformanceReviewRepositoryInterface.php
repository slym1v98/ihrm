<?php

namespace App\Modules\Performance\Domain\Repositories;

use App\Modules\Performance\Domain\Aggregates\PerformanceReview\PerformanceReview;
use App\Modules\Performance\Domain\Aggregates\PerformanceReview\PerformanceReviewId;

interface PerformanceReviewRepositoryInterface
{
    public function findById(PerformanceReviewId $id): ?PerformanceReview;

    public function findByCycleAndEmployee(string $cycleId, string $employeeId): ?PerformanceReview;

    public function findByCycleId(string $cycleId): array;

    public function save(PerformanceReview $review): void;
}
