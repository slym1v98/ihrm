<?php

namespace App\Modules\Onboarding\Application\Queries;

class ListTemplatesQuery
{
    public function __construct(
        public readonly ?string $departmentId = null,
        public readonly ?string $positionId = null,
        public readonly ?string $locationId = null,
        public readonly ?string $employmentType = null,
    ) {}
}
