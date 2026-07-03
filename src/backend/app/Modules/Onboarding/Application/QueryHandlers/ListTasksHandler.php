<?php

namespace App\Modules\Onboarding\Application\QueryHandlers;

use App\Modules\Onboarding\Application\Queries\ListTasksQuery;
use App\Modules\Onboarding\Domain\Repositories\OnboardingTaskRepositoryInterface;

class ListTasksHandler
{
    public function __construct(
        private readonly OnboardingTaskRepositoryInterface $taskRepo,
    ) {}

    public function handle(ListTasksQuery $query): array
    {
        return $this->taskRepo->findByPlanId($query->planId);
    }
}
