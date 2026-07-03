<?php

namespace App\Modules\Offboarding\Domain\Exceptions;

class AssetObligationsNotMetException extends \RuntimeException
{
    public function __construct(array $pending = [])
    {
        parent::__construct('Asset obligations not met: ' . implode(', ', $pending));
    }
}
