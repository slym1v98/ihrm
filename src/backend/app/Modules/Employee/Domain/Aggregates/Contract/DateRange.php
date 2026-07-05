<?php

namespace App\Modules\Employee\Domain\Aggregates\Contract;

use DateTimeImmutable;

final readonly class DateRange
{
    public function __construct(
        public DateTimeImmutable $start,
        public ?DateTimeImmutable $end = null,
    ) {}

    public function includes(DateTimeImmutable $date): bool
    {
        if ($date < $this->start) {
            return false;
        }
        if ($this->end !== null && $date > $this->end) {
            return false;
        }

        return true;
    }

    public function overlaps(self $other): bool
    {
        if ($this->end === null) {
            return $other->end === null || $other->end >= $this->start;
        }
        if ($other->end === null) {
            return $this->end >= $other->start;
        }

        return $this->start <= $other->end && $other->start <= $this->end;
    }
}
