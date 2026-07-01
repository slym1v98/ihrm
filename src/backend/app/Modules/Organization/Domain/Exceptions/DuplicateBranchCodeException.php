<?php

namespace App\Modules\Organization\Domain\Exceptions;

class DuplicateBranchCodeException extends \DomainException
{
    public function __construct(string $code = '')
    {
        parent::__construct("Branch code already exists: {$code}");
    }
}
