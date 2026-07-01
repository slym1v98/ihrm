<?php

namespace App\Modules\Organization\Domain\Exceptions;

class DepartmentNotFoundException extends \DomainException
{
    public function __construct(string $id = '')
    {
        parent::__construct("Department not found: {$id}");
    }
}
