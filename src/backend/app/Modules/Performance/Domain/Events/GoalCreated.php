<?php

namespace App\Modules\Performance\Domain\Events;

class GoalCreated
{
    public function __construct(
        public readonly string $goalId,
    ) {}
}
