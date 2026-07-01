<?php

namespace App\Modules\Organization\Domain\Events;

use App\Modules\Organization\Domain\Aggregates\Department\DepartmentId;
use DateTimeImmutable;

final readonly class DepartmentMoved
{
    public function __construct(
        public DepartmentId $departmentId,
        public ?DepartmentId $oldParentId,
        public ?DepartmentId $newParentId,
        public DateTimeImmutable $occurredAt,
    ) {}
}
