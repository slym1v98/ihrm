<?php

namespace App\Modules\Organization\Domain\Exceptions;

class PositionNotFoundException extends \DomainException
{
    public function __construct(string $id = '')
    {
        parent::__construct("Position not found: {$id}");
    }
}
