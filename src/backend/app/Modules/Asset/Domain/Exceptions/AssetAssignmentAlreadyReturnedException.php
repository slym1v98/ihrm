<?php

namespace App\Modules\Asset\Domain\Exceptions;

use RuntimeException;

class AssetAssignmentAlreadyReturnedException extends RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct("Asset assignment already returned: {$id}");
    }
}
