<?php

namespace App\Modules\Organization\Domain\Exceptions;

use App\Modules\Shared\Exceptions\AppException;

class DepartmentHasActiveChildrenException extends AppException
{
    public function __construct(string $param = '')
    {
        parent::__construct('DEPARTMENT_HAS_ACTIVE_CHILDREN', $param ? "DepartmentHasActiveChildrenException: $param" : 'DepartmentHasActiveChildrenException');
    }

    public function getHttpStatus(): int
    {
        return 409;
    }
}
