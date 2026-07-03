<?php

namespace App\Modules\Offboarding\Application\QueryHandlers;

use App\Modules\Offboarding\Application\Queries\ListRequestsQuery;
use App\Modules\Offboarding\Domain\Repositories\OffboardingRequestRepositoryInterface;

class ListRequestsHandler
{
    public function __construct(
        private readonly OffboardingRequestRepositoryInterface $requestRepo,
    ) {}

    public function handle(ListRequestsQuery $query): array
    {
        return $query->employeeId
            ? $this->requestRepo->findByEmployeeId($query->employeeId)
            : $this->requestRepo->all();
    }
}
