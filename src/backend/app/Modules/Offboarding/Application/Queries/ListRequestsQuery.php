<?php

namespace App\Modules\Offboarding\Application\Queries;

class ListRequestsQuery
{
    public function __construct(
        public readonly ?string $employeeId = null,
    ) {}
}
