<?php

namespace App\Modules\Offboarding\Domain\Exceptions;

class InvalidStatusTransitionException extends \RuntimeException
{
    public function __construct(string $from, string $to)
    {
        parent::__construct("Invalid status transition from '{$from}' to '{$to}'");
    }
}
