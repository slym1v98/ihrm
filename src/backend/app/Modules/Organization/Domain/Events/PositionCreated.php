<?php

namespace App\Modules\Organization\Domain\Events;

use App\Modules\Organization\Domain\Aggregates\Position\PositionId;
use DateTimeImmutable;

final readonly class PositionCreated
{
    public function __construct(
        public PositionId $positionId,
        public string $code,
        public string $name,
        public DateTimeImmutable $occurredAt,
    ) {}
}
