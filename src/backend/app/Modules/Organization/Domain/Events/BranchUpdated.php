<?php

namespace App\Modules\Organization\Domain\Events;

use App\Modules\Organization\Domain\Aggregates\Branch\BranchId;
use DateTimeImmutable;

final readonly class BranchUpdated
{
    public function __construct(
        public BranchId $branchId,
        public DateTimeImmutable $occurredAt,
    ) {}
}
