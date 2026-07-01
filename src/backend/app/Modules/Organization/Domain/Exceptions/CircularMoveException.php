<?php

namespace App\Modules\Organization\Domain\Exceptions;

class CircularMoveException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Cannot move department: target is self or a descendant.');
    }
}
