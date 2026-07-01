<?php

namespace App\Modules\Organization\Application\Queries\Department;

readonly class ListDepartmentsQuery
{
    public function __construct(
        public ?string $branchId = null,
        public ?string $parentId = null,
        public ?string $status = null,
        public int $page = 1,
        public int $perPage = 15,
    ) {}
}
