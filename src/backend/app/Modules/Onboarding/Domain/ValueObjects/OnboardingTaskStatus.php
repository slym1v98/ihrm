<?php

namespace App\Modules\Onboarding\Domain\ValueObjects;

enum OnboardingTaskStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Waived = 'waived';

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Pending => in_array($target, [self::InProgress, self::Waived], true),
            self::InProgress => in_array($target, [self::Completed, self::Waived], true),
            self::Completed, self::Waived => false,
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Waived], true);
    }
}
