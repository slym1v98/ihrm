<?php

namespace App\Modules\Organization\Application\QueryHandlers\Department;

use App\Modules\Organization\Application\Queries\Department\GetDepartmentQuery;
use App\Modules\Organization\Domain\Aggregates\Department\Department;
use App\Modules\Organization\Domain\Repositories\DepartmentRepositoryInterface;

class GetDepartmentHandler
{
    public function __construct(private DepartmentRepositoryInterface $departmentRepository) {}

    public function handle(GetDepartmentQuery $query): Department
    {
        return $this->departmentRepository->findById($query->id);
    }
}
