<?php

namespace App\Modules\Asset\Domain\Events;

use App\Modules\Asset\Domain\ValueObjects\AssetAssignmentId;
use App\Modules\Asset\Domain\ValueObjects\AssetItemId;

class AssetAssigned
{
    public function __construct(
        public readonly AssetAssignmentId $assetAssignmentId,
        public readonly AssetItemId $assetItemId,
        public readonly string $employeeId,
    ) {}
}
