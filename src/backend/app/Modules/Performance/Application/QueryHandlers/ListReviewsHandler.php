<?php

namespace App\Modules\Performance\Application\QueryHandlers;

use App\Modules\Performance\Application\Queries\ListReviewsQuery;
use App\Modules\Performance\Domain\Repositories\PerformanceReviewRepositoryInterface;

class ListReviewsHandler
{
    public function __construct(private readonly PerformanceReviewRepositoryInterface $repo) {}

    public function handle(ListReviewsQuery $q): array
    {
        return $this->repo->findByCycleId($q->cycleId);
    }
}
