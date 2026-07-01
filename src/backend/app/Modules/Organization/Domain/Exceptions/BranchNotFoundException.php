<?php

namespace App\Modules\Organization\Domain\Exceptions;

use App\Modules\Shared\Exceptions\AppException;

class BranchNotFoundException extends AppException
{
    public function __construct(string $param = '')
    {
        parent::__construct('BRANCH_NOT_FOUND', $param ? "BranchNotFoundException: $param" : 'BranchNotFoundException');
    }

    public function getHttpStatus(): int
    {
        return 404;
    }
}
