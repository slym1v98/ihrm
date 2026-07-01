<?php

namespace App\Modules\Organization\Application\Queries\Position;

readonly class ListPositionsQuery
{
    public function __construct(
        public ?string $status = null,
        public int $page = 1,
        public int $perPage = 15,
    ) {}
}
