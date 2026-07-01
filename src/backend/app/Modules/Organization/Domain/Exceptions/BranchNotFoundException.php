<?php

namespace App\Modules\Organization\Domain\Exceptions;

class BranchNotFoundException extends \DomainException
{
    public function __construct(string $id = '')
    {
        parent::__construct("Branch not found: {$id}");
    }
}
