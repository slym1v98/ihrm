<?php

namespace App\Modules\Organization\Application\Queries\Branch;

use App\Modules\Organization\Domain\Aggregates\Branch\BranchId;

readonly class GetBranchQuery
{
    public function __construct(public BranchId $id) {}
}
