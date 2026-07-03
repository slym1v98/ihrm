<?php

namespace App\Modules\Onboarding\Application\Queries;

class ListTasksQuery
{
    public function __construct(
        public readonly string $planId,
    ) {}
}
