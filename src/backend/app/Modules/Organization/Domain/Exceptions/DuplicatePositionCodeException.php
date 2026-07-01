<?php

namespace App\Modules\Organization\Domain\Exceptions;

use App\Modules\Shared\Exceptions\AppException;

class DuplicatePositionCodeException extends AppException
{
    public function __construct(string $param = '')
    {
        parent::__construct('DUPLICATE_POSITION_CODE', $param ? "DuplicatePositionCodeException: $param" : 'DuplicatePositionCodeException');
    }

    public function getHttpStatus(): int
    {
        return 409;
    }
}
