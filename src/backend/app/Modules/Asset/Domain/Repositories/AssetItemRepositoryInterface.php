<?php
namespace App\Modules\Asset\Domain\Repositories;

use App\Modules\Asset\Domain\Aggregates\AssetItem\AssetItem;
use App\Modules\Asset\Domain\ValueObjects\AssetItemId;

interface AssetItemRepositoryInterface
{
    public function findById(AssetItemId $id): ?AssetItem;
    public function findByAssetCode(string $assetCode): ?AssetItem;
    public function save(AssetItem $item): void;
    public function delete(AssetItem $item): void;
    public function all(array $filters = []): array;
}
