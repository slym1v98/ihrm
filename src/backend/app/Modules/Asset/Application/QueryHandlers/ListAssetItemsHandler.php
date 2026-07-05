<?php

namespace App\Modules\Asset\Application\QueryHandlers;

use App\Modules\Asset\Application\Queries\ListAssetItemsQuery;
use App\Modules\Asset\Domain\Repositories\AssetItemRepositoryInterface;

class ListAssetItemsHandler
{
    public function __construct(
        private readonly AssetItemRepositoryInterface $repo,
    ) {}

    public function handle(ListAssetItemsQuery $query): array
    {
        return $this->repo->all([
            'status' => $query->status,
            'asset_type' => $query->assetType,
        ]);
    }
}
