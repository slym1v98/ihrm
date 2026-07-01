<?php

namespace App\Modules\Organization\Application\Commands\Department;

use App\Modules\Organization\Domain\Aggregates\Branch\BranchId;
use App\Modules\Organization\Domain\Aggregates\Department\DepartmentCode;
use App\Modules\Organization\Domain\Aggregates\Department\DepartmentId;
use App\Modules\Organization\Domain\Aggregates\Department\DepartmentName;

readonly class CreateDepartmentCommand
{
    public function __construct(
        public BranchId $branchId,
        public DepartmentCode $code,
        public DepartmentName $name,
        public ?DepartmentId $parentId = null,
    ) {}
}
