<?php

namespace App\Modules\Performance\Application\Queries;

class ListGoalsQuery
{
    public function __construct(
        public readonly string $cycleId,
        public readonly ?string $employeeId = null,
    ) {}
}
