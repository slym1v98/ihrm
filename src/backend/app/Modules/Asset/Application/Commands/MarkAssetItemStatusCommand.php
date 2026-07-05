<?php

namespace App\Modules\Asset\Application\Commands;

use App\Modules\Asset\Domain\ValueObjects\AssetItemStatus;

class MarkAssetItemStatusCommand
{
    public function __construct(
        public readonly string $id,
        public readonly AssetItemStatus $newStatus,
    ) {}
}
