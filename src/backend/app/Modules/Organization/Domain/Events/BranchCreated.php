<?php

namespace App\Modules\Organization\Domain\Events;

use App\Modules\Organization\Domain\Aggregates\Branch\BranchId;
use DateTimeImmutable;

final readonly class BranchCreated
{
    public function __construct(
        public BranchId $branchId,
        public string $code,
        public string $name,
        public DateTimeImmutable $occurredAt,
    ) {}
}
