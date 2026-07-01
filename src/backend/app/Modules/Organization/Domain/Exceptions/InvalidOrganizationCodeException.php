<?php

namespace App\Modules\Organization\Domain\Exceptions;

use App\Modules\Shared\Exceptions\AppException;

class InvalidOrganizationCodeException extends AppException
{
    public function __construct(string $param = '')
    {
        parent::__construct('INVALID_ORGANIZATION_CODE', $param ? "InvalidOrganizationCodeException: $param" : 'InvalidOrganizationCodeException');
    }

    public function getHttpStatus(): int
    {
        return 400;
    }
}
