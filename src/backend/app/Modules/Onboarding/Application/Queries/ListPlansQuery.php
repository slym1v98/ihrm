<?php

namespace App\Modules\Onboarding\Application\Queries;

class ListPlansQuery
{
    public function __construct(
        public readonly ?string $employeeId = null,
    ) {}
}
