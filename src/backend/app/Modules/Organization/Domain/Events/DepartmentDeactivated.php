<?php

namespace App\Modules\Organization\Domain\Events;

use App\Modules\Organization\Domain\Aggregates\Department\DepartmentId;
use DateTimeImmutable;

final readonly class DepartmentDeactivated
{
    public function __construct(
        public DepartmentId $departmentId,
        public DateTimeImmutable $occurredAt,
    ) {}
}
