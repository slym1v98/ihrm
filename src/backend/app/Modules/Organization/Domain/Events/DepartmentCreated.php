<?php

namespace App\Modules\Organization\Domain\Events;

use App\Modules\Organization\Domain\Aggregates\Department\DepartmentId;
use DateTimeImmutable;

final readonly class DepartmentCreated
{
    public function __construct(
        public DepartmentId $departmentId,
        public string $code,
        public string $name,
        public DateTimeImmutable $occurredAt,
    ) {}
}
