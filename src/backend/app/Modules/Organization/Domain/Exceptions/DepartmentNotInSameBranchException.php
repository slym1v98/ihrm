<?php

namespace App\Modules\Organization\Domain\Exceptions;

use App\Modules\Shared\Exceptions\AppException;

class DepartmentNotInSameBranchException extends AppException
{
    public function __construct(string $param = '')
    {
        parent::__construct('DEPARTMENT_NOT_IN_SAME_BRANCH', $param ? "DepartmentNotInSameBranchException: $param" : 'DepartmentNotInSameBranchException');
    }

    public function getHttpStatus(): int
    {
        return 422;
    }
}
