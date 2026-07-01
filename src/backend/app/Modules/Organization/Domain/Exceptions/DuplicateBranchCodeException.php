<?php

namespace App\Modules\Organization\Domain\Exceptions;

use App\Modules\Shared\Exceptions\AppException;

class DuplicateBranchCodeException extends AppException
{
    public function __construct(string $param = '')
    {
        parent::__construct('DUPLICATE_BRANCH_CODE', $param ? "DuplicateBranchCodeException: $param" : 'DuplicateBranchCodeException');
    }

    public function getHttpStatus(): int
    {
        return 409;
    }
}
