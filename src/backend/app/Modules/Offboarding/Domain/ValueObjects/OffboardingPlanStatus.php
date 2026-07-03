<?php

namespace App\Modules\Offboarding\Domain\ValueObjects;

enum OffboardingPlanStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Draft => in_array($target, [self::Active, self::Cancelled], true),
            self::Active => in_array($target, [self::Completed, self::Cancelled], true),
            self::Completed, self::Cancelled => false,
        };
    }
}
