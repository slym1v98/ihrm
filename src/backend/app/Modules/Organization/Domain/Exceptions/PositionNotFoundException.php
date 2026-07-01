<?php

namespace App\Modules\Organization\Domain\Exceptions;

use App\Modules\Shared\Exceptions\AppException;

class PositionNotFoundException extends AppException
{
    public function __construct(string $param = '')
    {
        parent::__construct('POSITION_NOT_FOUND', $param ? "PositionNotFoundException: $param" : 'PositionNotFoundException');
    }

    public function getHttpStatus(): int
    {
        return 404;
    }
}
