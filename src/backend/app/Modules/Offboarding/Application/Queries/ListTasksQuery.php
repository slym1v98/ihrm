<?php

namespace App\Modules\Offboarding\Application\Queries;

class ListTasksQuery
{
    public function __construct(
        public readonly string $planId,
    ) {}
}
