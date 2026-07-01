<?php

namespace App\Modules\Organization\Domain\Exceptions;

class DuplicatePositionCodeException extends \DomainException
{
    public function __construct(string $code = '')
    {
        parent::__construct("Position code already exists: {$code}");
    }
}
