<?php
namespace App\Modules\Asset\Infrastructure\Persistence\Eloquent\Repositories;

use App\Modules\Asset\Domain\Aggregates\AssetAssignment\AssetAssignment;
use App\Modules\Asset\Domain\Repositories\AssetAssignmentRepositoryInterface;
use App\Modules\Asset\Domain\ValueObjects\AssetAssignmentId;
use App\Modules\Asset\Domain\ValueObjects\AssetAssignmentStatus;
use App\Modules\Asset\Domain\ValueObjects\AssetCondition;
use App\Modules\Asset\Domain\ValueObjects\AssetItemId;
use App\Modules\Asset\Infrastructure\Persistence\Eloquent\Models\AssetAssignmentModel;

class EloquentAssetAssignmentRepository implements AssetAssignmentRepositoryInterface
{
    public function findById(AssetAssignmentId $id): ?AssetAssignment
    {
        $model = AssetAssignmentModel::find($id->value);
        return $model ? $this->toDomain($model) : null;
    }

    public function findActiveByAsset(AssetItemId $assetItemId): ?AssetAssignment
    {
        $model = AssetAssignmentModel::where('asset_item_id', $assetItemId->value)
            ->where('status', AssetAssignmentStatus::Active->value)
            ->first();
        return $model ? $this->toDomain($model) : null;
    }

    public function findActiveByEmployee(string $employeeId): array
    {
        return AssetAssignmentModel::where('employee_id', $employeeId)
            ->where('status', AssetAssignmentStatus::Active->value)
            ->get()
            ->map(fn(AssetAssignmentModel $m) => $this->toDomain($m))
            ->toArray();
    }

    public function save(AssetAssignment $assignment): void
    {
        AssetAssignmentModel::updateOrCreate(
            ['id' => $assignment->getId()->value],
            [
                'asset_item_id' => $assignment->getAssetItemId()->value,
                'employee_id' => $assignment->getEmployeeId(),
                'issued_at' => $assignment->getIssuedAt(),
                'expected_return_at' => $assignment->getExpectedReturnAt(),
                'condition_on_issue' => $assignment->getConditionOnIssue()->value,
                'status' => $assignment->getStatus()->value,
            ]
        );
    }

    public function all(array $filters = []): array
    {
        $query = AssetAssignmentModel::query();
        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }
        if (!empty($filters['asset_item_id'])) {
            $query->where('asset_item_id', $filters['asset_item_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        return $query->get()->map(fn(AssetAssignmentModel $m) => $this->toDomain($m))->toArray();
    }

    private function toDomain(AssetAssignmentModel $model): AssetAssignment
    {
        return AssetAssignment::reconstitute(
            AssetAssignmentId::fromString($model->id),
            AssetItemId::fromString($model->asset_item_id),
            $model->employee_id,
            $model->issued_at->toDateTimeImmutable(),
            $model->expected_return_at?->toDateTimeImmutable(),
            AssetCondition::from($model->condition_on_issue),
            AssetAssignmentStatus::from($model->status),
            $model->created_at?->toDateTimeImmutable(),
            $model->updated_at?->toDateTimeImmutable(),
        );
    }
}
