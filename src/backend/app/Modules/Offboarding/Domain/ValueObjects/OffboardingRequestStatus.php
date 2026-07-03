<?php

namespace App\Modules\Offboarding\Domain\ValueObjects;

enum OffboardingRequestStatus: string
{
    case Draft = 'draft';
    case PendingApproval = 'pending_approval';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Draft => in_array($target, [self::PendingApproval, self::Cancelled], true),
            self::PendingApproval => in_array($target, [self::Approved, self::Rejected, self::Cancelled], true),
            self::Approved, self::Rejected, self::Cancelled => false,
        };
    }
}
