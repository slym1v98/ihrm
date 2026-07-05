<?php

namespace App\Modules\Performance\Domain\ValueObjects;

enum GoalStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
    case Archived = 'archived';

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Active => in_array($target, [self::Completed, self::Archived], true),
            self::Completed, self::Archived => false,
        };
    }
}
