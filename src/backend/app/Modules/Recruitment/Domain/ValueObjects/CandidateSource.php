<?php
namespace App\Modules\Recruitment\Domain\ValueObjects;
enum CandidateSource: string {
    case Referral = 'referral';
    case Linkedin = 'linkedin';
    case Website = 'website';
    case Agency = 'agency';
    case Manual = 'manual';
}
