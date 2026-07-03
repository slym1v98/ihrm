<?php
namespace App\Modules\Performance\Domain\Exceptions;

class InvalidStatusTransitionException extends \RuntimeException
{
    public function __construct(string $from, string $to)
    {
        parent::__construct("Invalid transition from {$from} to {$to}");
    }
}
