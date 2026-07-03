<?php
namespace App\Modules\Asset\Application\QueryHandlers;

use App\Modules\Asset\Application\Queries\GetEmployeeObligationsQuery;
use App\Modules\Asset\Domain\Repositories\AssetAssignmentRepositoryInterface;

class GetEmployeeObligationsHandler
{
    public function __construct(
        private readonly AssetAssignmentRepositoryInterface $assignmentRepo,
    ) {}

    public function handle(GetEmployeeObligationsQuery $query): array
    {
        return $this->assignmentRepo->findActiveByEmployee($query->employeeId);
    }
}
