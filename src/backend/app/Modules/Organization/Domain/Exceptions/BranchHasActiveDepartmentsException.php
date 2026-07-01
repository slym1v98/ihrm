<?php

namespace App\Modules\Organization\Domain\Exceptions;

class BranchHasActiveDepartmentsException extends \DomainException
{
    public function __construct(string $branchId = '')
    {
        parent::__construct("Cannot deactivate branch {$branchId}: it has active departments.");
    }
}
