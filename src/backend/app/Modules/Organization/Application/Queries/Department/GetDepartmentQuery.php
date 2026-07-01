<?php

namespace App\Modules\Organization\Application\Queries\Department;

use App\Modules\Organization\Domain\Aggregates\Department\DepartmentId;

readonly class GetDepartmentQuery
{
    public function __construct(public DepartmentId $id) {}
}
