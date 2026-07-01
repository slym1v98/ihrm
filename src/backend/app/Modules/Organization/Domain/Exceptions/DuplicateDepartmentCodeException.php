<?php

namespace App\Modules\Organization\Domain\Exceptions;

class DuplicateDepartmentCodeException extends \DomainException
{
    public function __construct(string $code = '')
    {
        parent::__construct("Department code already exists: {$code}");
    }
}
