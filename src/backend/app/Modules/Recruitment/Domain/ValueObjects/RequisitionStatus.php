<?php
namespace App\Modules\Recruitment\Domain\ValueObjects;
enum RequisitionStatus: string {
    case Draft = 'draft';
    case PendingApproval = 'pending_approval';
    case Open = 'open';
    case OnHold = 'on_hold';
    case Closed = 'closed';
    case Cancelled = 'cancelled';
    public function canTransitionTo(self $target): bool {
        return match($this) {
            self::Draft => in_array($target, [self::PendingApproval, self::Cancelled]),
            self::PendingApproval => in_array($target, [self::Open, self::Cancelled]),
            self::Open => in_array($target, [self::OnHold, self::Closed, self::Cancelled]),
            self::OnHold => in_array($target, [self::Open, self::Closed, self::Cancelled]),
            self::Closed, self::Cancelled => false,
        };
    }
}
