<?php

namespace App\Modules\Performance\Application\CommandHandlers;

use App\Modules\Performance\Application\Commands\FinalizeReviewCommand;
use App\Modules\Performance\Domain\Aggregates\PerformanceReview\PerformanceReviewId;
use App\Modules\Performance\Domain\Repositories\PerformanceReviewRepositoryInterface;
use App\Modules\Performance\Domain\Exceptions\PerformanceReviewNotFoundException;

class FinalizeReviewHandler
{
    public function __construct(private readonly PerformanceReviewRepositoryInterface $repo) {}
    public function handle(FinalizeReviewCommand $cmd): void
    {
        $review = $this->repo->findById(PerformanceReviewId::fromString($cmd->id)) ?? throw new PerformanceReviewNotFoundException($cmd->id);
        $review->finalize($cmd->finalScore);
        $this->repo->save($review);
    }
}
