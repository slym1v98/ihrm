<?php
namespace App\Modules\Recruitment\Domain\ValueObjects;
enum InterviewStatus: string {
    case Scheduled = 'scheduled';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
