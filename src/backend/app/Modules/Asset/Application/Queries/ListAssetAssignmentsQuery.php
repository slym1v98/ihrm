<?php

namespace App\Modules\Asset\Application\Queries;

class ListAssetAssignmentsQuery
{
    public function __construct(
        public readonly ?string $employeeId = null,
        public readonly ?string $assetItemId = null,
        public readonly ?string $status = null,
    ) {}
}
