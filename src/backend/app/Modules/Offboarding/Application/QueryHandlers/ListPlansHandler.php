<?php

namespace App\Modules\Offboarding\Application\QueryHandlers;

use App\Modules\Offboarding\Application\Queries\ListPlansQuery;
use App\Modules\Offboarding\Domain\Repositories\OffboardingPlanRepositoryInterface;

class ListPlansHandler
{
    public function __construct(
        private readonly OffboardingPlanRepositoryInterface $planRepo,
    ) {}

    public function handle(ListPlansQuery $query): array
    {
        return $query->employeeId
            ? $this->planRepo->findByEmployeeId($query->employeeId)
            : $this->planRepo->all();
    }
}
