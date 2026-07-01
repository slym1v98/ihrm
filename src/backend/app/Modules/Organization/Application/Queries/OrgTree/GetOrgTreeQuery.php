<?php

namespace App\Modules\Organization\Application\Queries\OrgTree;

readonly class GetOrgTreeQuery
{
    public function __construct(public ?string $branchId = null) {}
}
