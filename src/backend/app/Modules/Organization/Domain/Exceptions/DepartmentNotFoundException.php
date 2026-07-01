<?php

namespace App\Modules\Organization\Domain\Exceptions;

use App\Modules\Shared\Exceptions\AppException;

class DepartmentNotFoundException extends AppException
{
    public function __construct(string $param = '')
    {
        parent::__construct('DEPARTMENT_NOT_FOUND', $param ? "DepartmentNotFoundException: $param" : 'DepartmentNotFoundException');
    }

    public function getHttpStatus(): int
    {
        return 404;
    }
}
