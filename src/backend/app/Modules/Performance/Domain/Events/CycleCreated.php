<?php

namespace App\Modules\Performance\Domain\Events;

class CycleCreated
{
    public function __construct(
        public readonly string $cycleId,
    ) {}
}
