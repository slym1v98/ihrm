<?php

namespace App\Modules\Organization\Domain\Exceptions;

use App\Modules\Shared\Exceptions\AppException;

class DuplicateDepartmentCodeException extends AppException
{
    public function __construct(string $param = '')
    {
        parent::__construct('DUPLICATE_DEPARTMENT_CODE', $param ? "DuplicateDepartmentCodeException: $param" : 'DuplicateDepartmentCodeException');
    }

    public function getHttpStatus(): int
    {
        return 409;
    }
}
