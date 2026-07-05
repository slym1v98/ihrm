<?php

namespace App\Modules\Training\Domain\ValueObjects;

enum EnrollmentStatus: string
{
    case Enrolled = 'enrolled';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Enrolled => in_array($target, [self::Completed, self::Cancelled], true), self::Completed, self::Cancelled => false,
        };
    }
}
