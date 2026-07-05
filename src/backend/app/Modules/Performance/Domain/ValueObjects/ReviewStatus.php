<?php

namespace App\Modules\Performance\Domain\ValueObjects;

enum ReviewStatus: string
{
    case PendingSelf = 'pending_self';
    case SelfCompleted = 'self_completed';
    case ManagerCompleted = 'manager_completed';
    case HrCompleted = 'hr_completed';
    case Finalized = 'finalized';

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::PendingSelf => $target === self::SelfCompleted,
            self::SelfCompleted => $target === self::ManagerCompleted,
            self::ManagerCompleted => $target === self::HrCompleted,
            self::HrCompleted => $target === self::Finalized,
            self::Finalized => false,
        };
    }
}
