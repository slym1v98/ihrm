<?php

namespace App\Modules\Performance\Domain\Events;

class GoalCompleted
{
    public function __construct(
        public readonly string $goalId,
    ) {}
}
