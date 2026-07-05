<?php

namespace App\Modules\Asset\Domain\Repositories;

use App\Modules\Asset\Domain\Aggregates\AssetReturn\AssetReturn;
use App\Modules\Asset\Domain\ValueObjects\AssetReturnId;

interface AssetReturnRepositoryInterface
{
    public function findById(AssetReturnId $id): ?AssetReturn;

    public function save(AssetReturn $return): void;
}
