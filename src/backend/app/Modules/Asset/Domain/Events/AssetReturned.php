<?php

namespace App\Modules\Asset\Domain\Events;

use App\Modules\Asset\Domain\ValueObjects\AssetAssignmentId;
use App\Modules\Asset\Domain\ValueObjects\AssetReturnId;

class AssetReturned
{
    public function __construct(
        public readonly AssetReturnId $assetReturnId,
        public readonly AssetAssignmentId $assetAssignmentId,
    ) {}
}
