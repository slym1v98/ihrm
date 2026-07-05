<?php

namespace App\Modules\Recruitment\Domain\ValueObjects;

enum CandidateStatus: string
{
    case New = 'new';
    case Screening = 'screening';
    case Interviewing = 'interviewing';
    case Offered = 'offered';
    case Hired = 'hired';
    case Rejected = 'rejected';
    case Archived = 'archived';

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::New => in_array($target, [self::Screening, self::Rejected, self::Archived]),
            self::Screening => in_array($target, [self::Interviewing, self::Rejected, self::Archived]),
            self::Interviewing => in_array($target, [self::Offered, self::Rejected, self::Archived]),
            self::Offered => in_array($target, [self::Hired, self::Rejected, self::Archived]),
            self::Hired, self::Rejected, self::Archived => false,
        };
    }
}
