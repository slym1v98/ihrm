<?php

namespace App\Modules\Onboarding\Application\QueryHandlers;

use App\Modules\Onboarding\Application\Queries\ListPlansQuery;
use App\Modules\Onboarding\Domain\Repositories\OnboardingPlanRepositoryInterface;

class ListPlansHandler
{
    public function __construct(
        private readonly OnboardingPlanRepositoryInterface $planRepo,
    ) {}

    public function handle(ListPlansQuery $query): array
    {
        return $query->employeeId
            ? $this->planRepo->findByEmployeeId($query->employeeId)
            : $this->planRepo->all();
    }
}
