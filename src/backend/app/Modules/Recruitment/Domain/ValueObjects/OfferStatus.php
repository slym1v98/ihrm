<?php
namespace App\Modules\Recruitment\Domain\ValueObjects;
enum OfferStatus: string {
    case Draft = 'draft';
    case Sent = 'sent';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Withdrawn = 'withdrawn';
    public function isTerminal(): bool { return in_array($this, [self::Accepted, self::Rejected, self::Withdrawn]); }
}
