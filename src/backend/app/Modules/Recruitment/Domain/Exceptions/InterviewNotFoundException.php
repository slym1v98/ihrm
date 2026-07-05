<?php

namespace App\Modules\Recruitment\Domain\Exceptions;

use App\Modules\Shared\Exceptions\AppException;

class InterviewNotFoundException extends AppException
{
    public function __construct(string $d = '')
    {
        parent::__construct('INTV_NOT_FOUND', trim('Interview not found: '.$d));
    }

    public function getHttpStatus(): int
    {
        return 404;
    }
}
