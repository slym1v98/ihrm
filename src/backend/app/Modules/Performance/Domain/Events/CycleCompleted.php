<?php

namespace App\Modules\Performance\Domain\Events;

class CycleCompleted
{
    public function __construct(
        public readonly string $cycleId,
    ) {}
}
