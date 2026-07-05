<?php

namespace App\Modules\Performance\Domain\Exceptions;

class PerformanceCycleNotFoundException extends \RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct("PerformanceCycle not found: {$id}");
    }
}
