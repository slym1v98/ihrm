<?php

namespace App\Modules\Asset\Domain\Exceptions;

use RuntimeException;

class AssetItemNotFoundException extends RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct("Asset item not found: {$id}");
    }
}
