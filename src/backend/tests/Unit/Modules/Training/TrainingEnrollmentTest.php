<?php

namespace Tests\Unit\Modules\Training;

use PHPUnit\Framework\TestCase;
use App\Modules\Training\Domain\Aggregates\TrainingEnrollment\TrainingEnrollment;
use App\Modules\Training\Domain\Aggregates\TrainingEnrollment\TrainingEnrollmentId;
use App\Modules\Training\Domain\ValueObjects\EnrollmentStatus;
use App\Modules\Training\Domain\Exceptions\InvalidEnrollmentStatusException;

class TrainingEnrollmentTest extends TestCase
{
    public function test_record_attendance_and_complete(): void
    {
        $e = TrainingEnrollment::enroll(TrainingEnrollmentId::generate(), 'session-1', 'employee-1', new \DateTimeImmutable('2026-08-01 08:00'));
        $e->recordAttendance(['present' => true, 'checked_in_at' => '2026-08-01 09:00:00']);
        $this->assertTrue($e->getAttendance()['present']);
        $e->complete();
        $this->assertSame(EnrollmentStatus::Completed, $e->getStatus());
    }

    public function test_cancelled_enrollment_cannot_record_attendance(): void
    {
        $e = TrainingEnrollment::enroll(TrainingEnrollmentId::generate(), 'session-1', 'employee-1', new \DateTimeImmutable('2026-08-01 08:00'));
        $e->cancel();
        $this->expectException(InvalidEnrollmentStatusException::class);
        $e->recordAttendance(['present' => true]);
    }
}
