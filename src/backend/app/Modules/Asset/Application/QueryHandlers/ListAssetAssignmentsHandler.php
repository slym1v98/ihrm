<?php

namespace App\Modules\Asset\Application\QueryHandlers;

use App\Modules\Asset\Application\Queries\ListAssetAssignmentsQuery;
use App\Modules\Asset\Domain\Repositories\AssetAssignmentRepositoryInterface;

class ListAssetAssignmentsHandler
{
    public function __construct(
        private readonly AssetAssignmentRepositoryInterface $repo,
    ) {}

    public function handle(ListAssetAssignmentsQuery $query): array
    {
        return $this->repo->all([
            'employee_id' => $query->employeeId,
            'asset_item_id' => $query->assetItemId,
            'status' => $query->status,
        ]);
    }
}
