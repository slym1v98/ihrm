<?php

namespace App\Modules\Employee\Domain\Aggregates\Employee;

use DateTimeImmutable;

final readonly class EmploymentSnapshot
{
    public function __construct(
        public ?string $branchId,
        public ?string $departmentId,
        public ?string $positionId,
        public DateTimeImmutable $effectiveAt = new DateTimeImmutable,
    ) {
    }
}
