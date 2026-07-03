<?php
namespace App\Modules\Asset\Infrastructure\Persistence\Eloquent\Repositories;

use App\Modules\Asset\Domain\Aggregates\AssetItem\AssetItem;
use App\Modules\Asset\Domain\Repositories\AssetItemRepositoryInterface;
use App\Modules\Asset\Domain\ValueObjects\AssetCondition;
use App\Modules\Asset\Domain\ValueObjects\AssetItemId;
use App\Modules\Asset\Domain\ValueObjects\AssetItemStatus;
use App\Modules\Asset\Infrastructure\Persistence\Eloquent\Models\AssetItemModel;

class EloquentAssetItemRepository implements AssetItemRepositoryInterface
{
    public function findById(AssetItemId $id): ?AssetItem
    {
        $model = AssetItemModel::find($id->value);
        return $model ? $this->toDomain($model) : null;
    }

    public function findByAssetCode(string $assetCode): ?AssetItem
    {
        $model = AssetItemModel::where('asset_code', $assetCode)->first();
        return $model ? $this->toDomain($model) : null;
    }

    public function save(AssetItem $item): void
    {
        AssetItemModel::updateOrCreate(
            ['id' => $item->getId()->value],
            [
                'asset_code' => $item->getAssetCode(),
                'asset_type' => $item->getAssetType(),
                'name' => $item->getName(),
                'serial_number' => $item->getSerialNumber(),
                'condition' => $item->getCondition()->value,
                'status' => $item->getStatus()->value,
                'notes' => $item->getNotes(),
            ]
        );
    }

    public function delete(AssetItem $item): void
    {
        AssetItemModel::destroy($item->getId()->value);
    }

    public function all(array $filters = []): array
    {
        $query = AssetItemModel::query();
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['asset_type'])) {
            $query->where('asset_type', $filters['asset_type']);
        }
        return $query->get()->map(fn(AssetItemModel $m) => $this->toDomain($m))->toArray();
    }

    private function toDomain(AssetItemModel $model): AssetItem
    {
        return AssetItem::reconstitute(
            AssetItemId::fromString($model->id),
            $model->asset_code,
            $model->asset_type,
            $model->name,
            $model->serial_number,
            AssetCondition::from($model->condition),
            AssetItemStatus::from($model->status),
            $model->notes,
            $model->created_at?->toDateTimeImmutable(),
            $model->updated_at?->toDateTimeImmutable(),
        );
    }
}
