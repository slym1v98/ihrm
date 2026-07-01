<?php

namespace App\Modules\Organization\Application\Commands\Department;

use App\Modules\Organization\Domain\Aggregates\Department\DepartmentId;

readonly class ActivateDepartmentCommand
{
    public function __construct(public DepartmentId $id) {}
}
