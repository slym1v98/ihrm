<?php

namespace App\Modules\Organization\Domain\Exceptions;

use App\Modules\Shared\Exceptions\AppException;

class CircularMoveException extends AppException
{
    public function __construct(string $param = '')
    {
        parent::__construct('CIRCULAR_MOVE', $param ? "CircularMoveException: $param" : 'CircularMoveException');
    }

    public function getHttpStatus(): int
    {
        return 422;
    }
}
