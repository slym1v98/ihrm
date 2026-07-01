<?php

namespace App\Modules\Organization\Domain\Exceptions;

use App\Modules\Shared\Exceptions\AppException;

class BranchHasActiveDepartmentsException extends AppException
{
    public function __construct(string $param = '')
    {
        parent::__construct('BRANCH_HAS_ACTIVE_DEPARTMENTS', $param ? "BranchHasActiveDepartmentsException: $param" : 'BranchHasActiveDepartmentsException');
    }

    public function getHttpStatus(): int
    {
        return 409;
    }
}
