<?php

namespace App\Modules\Organization\Domain\Exceptions;

class DepartmentHasActiveChildrenException extends \DomainException
{
    public function __construct(string $departmentId = '')
    {
        parent::__construct("Cannot deactivate department {$departmentId}: it has active child departments.");
    }
}
