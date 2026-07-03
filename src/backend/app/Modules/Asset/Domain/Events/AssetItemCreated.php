<?php

namespace App\Modules\Asset\Domain\Events;

use App\Modules\Asset\Domain\ValueObjects\AssetItemId;

class AssetItemCreated
{
    public function __construct(
        public readonly AssetItemId $assetItemId,
    ) {}
}
