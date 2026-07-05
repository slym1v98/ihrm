<?php

namespace App\Modules\Recruitment\Domain\Exceptions;

use App\Modules\Shared\Exceptions\AppException;

class InvalidStatusTransitionException extends AppException
{
    public function __construct(string $msg = '')
    {
        parent::__construct('INVALID_TRANSITION', $msg);
    }

    public function getHttpStatus(): int
    {
        return 422;
    }
}
