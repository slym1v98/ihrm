<?php

namespace App\Modules\Offboarding\Application\QueryHandlers;

use App\Modules\Offboarding\Application\Queries\ListTasksQuery;
use App\Modules\Offboarding\Domain\Repositories\OffboardingTaskRepositoryInterface;

class ListTasksHandler
{
    public function __construct(
        private readonly OffboardingTaskRepositoryInterface $taskRepo,
    ) {}

    public function handle(ListTasksQuery $query): array
    {
        return $this->taskRepo->findByPlanId($query->planId);
    }
}
