<?php

namespace App\Modules\Performance\Application\CommandHandlers;

use App\Modules\Performance\Application\Commands\SubmitManagerReviewCommand;
use App\Modules\Performance\Domain\Aggregates\PerformanceReview\PerformanceReviewId;
use App\Modules\Performance\Domain\Repositories\PerformanceReviewRepositoryInterface;
use App\Modules\Performance\Domain\Exceptions\PerformanceReviewNotFoundException;

class SubmitManagerReviewHandler
{
    public function __construct(private readonly PerformanceReviewRepositoryInterface $repo) {}
    public function handle(SubmitManagerReviewCommand $cmd): void
    {
        $review = $this->repo->findById(PerformanceReviewId::fromString($cmd->id)) ?? throw new PerformanceReviewNotFoundException($cmd->id);
        $review->submitManager($cmd->assessment);
        $this->repo->save($review);
    }
}
