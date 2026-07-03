<?php

namespace App\Modules\Performance\Domain\Events;

class CycleActivated
{
    public function __construct(
        public readonly string $cycleId,
    ) {}
}
