<?php

namespace App\Modules\Performance\Application\Queries;

class ListReviewsQuery
{
    public function __construct(
        public readonly string $cycleId,
    ) {}
}
