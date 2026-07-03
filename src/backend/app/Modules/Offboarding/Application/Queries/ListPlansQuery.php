<?php

namespace App\Modules\Offboarding\Application\Queries;

class ListPlansQuery
{
    public function __construct(
        public readonly ?string $employeeId = null,
    ) {}
}
