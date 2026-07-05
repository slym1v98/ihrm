<?php

namespace App\Modules\Recruitment\Domain\Exceptions;

use App\Modules\Shared\Exceptions\AppException;

class CandidateConversionException extends AppException
{
    public function __construct(string $d = '')
    {
        parent::__construct('CAND_CONV_FAILED', 'Conversion failed: '.$d);
    }

    public function getHttpStatus(): int
    {
        return 422;
    }
}
