<?php

namespace App\Modules\Training\Domain\Exceptions;

class InvalidEnrollmentStatusException extends \RuntimeException
{
    public function __construct(string $from, string $to)
    {
        parent::__construct("Cannot transition enrollment from $from to $to");
    }
}
