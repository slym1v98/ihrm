<?php

namespace App\Modules\Asset\Domain\Exceptions;

use RuntimeException;

class AssetAssignmentNotFoundException extends RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct("Asset assignment not found: {$id}");
    }
}
