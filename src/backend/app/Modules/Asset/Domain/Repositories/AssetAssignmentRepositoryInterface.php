<?php
namespace App\Modules\Asset\Domain\Repositories;

use App\Modules\Asset\Domain\Aggregates\AssetAssignment\AssetAssignment;
use App\Modules\Asset\Domain\ValueObjects\AssetAssignmentId;
use App\Modules\Asset\Domain\ValueObjects\AssetItemId;

interface AssetAssignmentRepositoryInterface
{
    public function findById(AssetAssignmentId $id): ?AssetAssignment;
    public function findActiveByAsset(AssetItemId $assetItemId): ?AssetAssignment;
    public function findActiveByEmployee(string $employeeId): array;
    public function save(AssetAssignment $assignment): void;
    public function all(array $filters = []): array;
}
