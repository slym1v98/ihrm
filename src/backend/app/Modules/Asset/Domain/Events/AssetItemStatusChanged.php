<?php

namespace App\Modules\Asset\Domain\Events;

use App\Modules\Asset\Domain\ValueObjects\AssetItemId;
use App\Modules\Asset\Domain\ValueObjects\AssetItemStatus;

class AssetItemStatusChanged
{
    public function __construct(
        public readonly AssetItemId $assetItemId,
        public readonly AssetItemStatus $oldStatus,
        public readonly AssetItemStatus $newStatus,
    ) {}
}
