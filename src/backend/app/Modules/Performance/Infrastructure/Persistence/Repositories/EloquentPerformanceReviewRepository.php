<?php

namespace App\Modules\Performance\Infrastructure\Persistence\Repositories;

use App\Modules\Performance\Domain\Aggregates\PerformanceReview\PerformanceReview;
use App\Modules\Performance\Domain\Aggregates\PerformanceReview\PerformanceReviewId;
use App\Modules\Performance\Domain\Repositories\PerformanceReviewRepositoryInterface;
use App\Modules\Performance\Domain\ValueObjects\ReviewStatus;
use App\Modules\Performance\Infrastructure\Persistence\Eloquent\PerformanceReviewModel;

class EloquentPerformanceReviewRepository implements PerformanceReviewRepositoryInterface
{
    public function findById(PerformanceReviewId $id): ?PerformanceReview
    {
        $m = PerformanceReviewModel::find($id->value);

        return $m ? $this->toDomain($m) : null;
    }

    public function findByCycleAndEmployee(string $cycleId, string $employeeId): ?PerformanceReview
    {
        $m = PerformanceReviewModel::where('cycle_id', $cycleId)->where('employee_id', $employeeId)->first();

        return $m ? $this->toDomain($m) : null;
    }

    public function findByCycleId(string $cycleId): array
    {
        return PerformanceReviewModel::where('cycle_id', $cycleId)->get()->map(fn ($m) => $this->toDomain($m))->toArray();
    }

    public function save(PerformanceReview $review): void
    {
        PerformanceReviewModel::updateOrCreate(['id' => $review->getId()->value], [
            'cycle_id' => $review->getCycleId(), 'employee_id' => $review->getEmployeeId(), 'self_assessment' => $review->getSelfAssessment(),
            'manager_assessment' => $review->getManagerAssessment(), 'hr_assessment' => $review->getHrAssessment(), 'final_score' => $review->getFinalScore(),
            'status' => $review->getStatus()->value, 'finalized_at' => $review->getFinalizedAt()?->format('Y-m-d H:i:s'),
        ]);
    }

    private function toDomain(PerformanceReviewModel $m): PerformanceReview
    {
        return PerformanceReview::reconstitute(PerformanceReviewId::fromString($m->id), $m->cycle_id, $m->employee_id, $m->self_assessment, $m->manager_assessment, $m->hr_assessment, $m->final_score === null ? null : (float) $m->final_score, ReviewStatus::from($m->status), $m->finalized_at ? new \DateTimeImmutable($m->finalized_at) : null);
    }
}
