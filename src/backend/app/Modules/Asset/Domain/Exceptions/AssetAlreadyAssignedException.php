<?php

namespace App\Modules\Asset\Domain\Exceptions;

use RuntimeException;

class AssetAlreadyAssignedException extends RuntimeException
{
    public function __construct(string $assetCode)
    {
        parent::__construct("Asset already has an active assignment: {$assetCode}");
    }
}
