<?php

namespace App\Modules\Organization\Domain\Events;

use App\Modules\Organization\Domain\Aggregates\Position\PositionId;
use DateTimeImmutable;

final readonly class PositionUpdated
{
    public function __construct(
        public PositionId $positionId,
        public DateTimeImmutable $occurredAt,
    ) {}
}
