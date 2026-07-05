<?php

namespace App\Modules\Training\Domain\ValueObjects;

enum SessionStatus: string
{
    case Scheduled = 'scheduled';
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Scheduled => in_array($target, [self::Active, self::Cancelled], true), self::Active => in_array($target, [self::Completed, self::Cancelled], true), self::Completed, self::Cancelled => false,
        };
    }
}
