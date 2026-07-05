<?php

namespace App\Modules\Asset\Application\Queries;

class ListAssetItemsQuery
{
    public function __construct(
        public readonly ?string $status = null,
        public readonly ?string $assetType = null,
    ) {}
}
