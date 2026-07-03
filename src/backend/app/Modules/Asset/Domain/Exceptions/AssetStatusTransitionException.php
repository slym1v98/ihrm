<?php

namespace App\Modules\Asset\Domain\Exceptions;

use RuntimeException;

class AssetStatusTransitionException extends RuntimeException
{
    public function __construct(string $from, string $to)
    {
        parent::__construct("Cannot transition asset from '{$from}' to '{$to}'");
    }
}
