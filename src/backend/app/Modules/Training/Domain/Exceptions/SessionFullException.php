<?php

namespace App\Modules\Training\Domain\Exceptions;

class SessionFullException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Session has reached maximum capacity');
    }
}
