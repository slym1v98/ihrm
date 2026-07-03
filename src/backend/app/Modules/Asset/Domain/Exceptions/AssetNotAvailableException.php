<?php

namespace App\Modules\Asset\Domain\Exceptions;

use RuntimeException;

class AssetNotAvailableException extends RuntimeException
{
    public function __construct(string $status)
    {
        parent::__construct("Asset is not available (status: {$status})");
    }
}
