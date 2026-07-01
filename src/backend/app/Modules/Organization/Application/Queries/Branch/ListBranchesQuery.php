<?php

namespace App\Modules\Organization\Application\Queries\Branch;

readonly class ListBranchesQuery
{
    public function __construct(
        public ?string $status = null,
        public int $page = 1,
        public int $perPage = 15,
    ) {}
}
