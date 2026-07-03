<?php

namespace App\Modules\Performance\Application\CommandHandlers;

use App\Modules\Performance\Application\Commands\CreateReviewCommand;
use App\Modules\Performance\Domain\Aggregates\PerformanceReview\PerformanceReview;
use App\Modules\Performance\Domain\Aggregates\PerformanceReview\PerformanceReviewId;
use App\Modules\Performance\Domain\Repositories\PerformanceReviewRepositoryInterface;

class CreateReviewHandler
{
    public function __construct(private readonly PerformanceReviewRepositoryInterface $repo) {}
    public function handle(CreateReviewCommand $cmd): PerformanceReview
    {
        $review = PerformanceReview::create(PerformanceReviewId::generate(), $cmd->cycleId, $cmd->employeeId);
        $this->repo->save($review);
        return $review;
    }
}
