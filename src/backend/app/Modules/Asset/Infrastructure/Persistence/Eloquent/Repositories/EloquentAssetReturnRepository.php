<?php

namespace App\Modules\Asset\Infrastructure\Persistence\Eloquent\Repositories;

use App\Modules\Asset\Domain\Aggregates\AssetReturn\AssetReturn;
use App\Modules\Asset\Domain\Repositories\AssetReturnRepositoryInterface;
use App\Modules\Asset\Domain\ValueObjects\AssetAssignmentId;
use App\Modules\Asset\Domain\ValueObjects\AssetCondition;
use App\Modules\Asset\Domain\ValueObjects\AssetReturnId;
use App\Modules\Asset\Infrastructure\Persistence\Eloquent\Models\AssetReturnModel;

class EloquentAssetReturnRepository implements AssetReturnRepositoryInterface
{
    public function findById(AssetReturnId $id): ?AssetReturn
    {
        $model = AssetReturnModel::find($id->value);

        return $model ? $this->toDomain($model) : null;
    }

    public function save(AssetReturn $return): void
    {
        AssetReturnModel::updateOrCreate(
            ['id' => $return->getId()->value],
            [
                'asset_assignment_id' => $return->getAssetAssignmentId()->value,
                'returned_at' => $return->getReturnedAt(),
                'condition_on_return' => $return->getConditionOnReturn()->value,
                'notes' => $return->getNotes(),
                'settlement_amount' => $return->getSettlementAmount(),
            ]
        );
    }

    private function toDomain(AssetReturnModel $model): AssetReturn
    {
        return AssetReturn::reconstitute(
            AssetReturnId::fromString($model->id),
            AssetAssignmentId::fromString($model->asset_assignment_id),
            $model->returned_at->toDateTimeImmutable(),
            AssetCondition::from($model->condition_on_return),
            $model->notes,
            (float) $model->settlement_amount,
            $model->created_at?->toDateTimeImmutable(),
            $model->updated_at?->toDateTimeImmutable(),
        );
    }
}
