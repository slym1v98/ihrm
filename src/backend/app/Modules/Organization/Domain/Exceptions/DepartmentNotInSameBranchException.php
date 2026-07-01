<?php

namespace App\Modules\Organization\Domain\Exceptions;

class DepartmentNotInSameBranchException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Cannot move department: parent department is in a different branch.');
    }
}
