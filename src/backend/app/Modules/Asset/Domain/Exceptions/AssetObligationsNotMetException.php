<?php

namespace App\Modules\Asset\Domain\Exceptions;

use RuntimeException;

class AssetObligationsNotMetException extends RuntimeException
{
    public function __construct(public readonly array $pendingAssets)
    {
        parent::__construct('Employee has unresolved asset obligations');
    }

    public function getPendingAssets(): array
    {
        return $this->pendingAssets;
    }
}
