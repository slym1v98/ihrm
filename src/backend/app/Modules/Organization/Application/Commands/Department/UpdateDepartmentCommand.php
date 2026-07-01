<?php

namespace App\Modules\Organization\Application\Commands\Department;

use App\Modules\Organization\Domain\Aggregates\Department\DepartmentId;
use App\Modules\Organization\Domain\Aggregates\Department\DepartmentName;

readonly class UpdateDepartmentCommand
{
    public function __construct(
        public DepartmentId $id,
        public DepartmentName $name,
        public ?string $managerEmployeeId = null,
    ) {}
}
