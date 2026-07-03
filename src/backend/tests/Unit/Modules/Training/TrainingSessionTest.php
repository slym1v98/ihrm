<?php

namespace Tests\Unit\Modules\Training;

use PHPUnit\Framework\TestCase;
use App\Modules\Training\Domain\Aggregates\TrainingSession\TrainingSession;
use App\Modules\Training\Domain\Aggregates\TrainingSession\TrainingSessionId;
use App\Modules\Training\Domain\ValueObjects\SessionStatus;
use App\Modules\Training\Domain\Exceptions\SessionFullException;

class TrainingSessionTest extends TestCase
{
    public function test_session_lifecycle(): void
    {
        $s = TrainingSession::schedule(TrainingSessionId::generate(), 'course-1', 'S1', 'Session 1', new \DateTimeImmutable('2026-08-01 09:00'), new \DateTimeImmutable('2026-08-01 17:00'), 'Room A', 'Trainer', 2);
        $this->assertSame(SessionStatus::Scheduled, $s->getStatus());
        $s->start();
        $this->assertSame(SessionStatus::Active, $s->getStatus());
        $s->complete();
        $this->assertSame(SessionStatus::Completed, $s->getStatus());
    }

    public function test_capacity_guard_rejects_extra_enrollment(): void
    {
        $s = TrainingSession::schedule(TrainingSessionId::generate(), 'course-1', 'S1', 'Session 1', new \DateTimeImmutable('2026-08-01 09:00'), new \DateTimeImmutable('2026-08-01 17:00'), null, null, 1);
        $s->assertCanEnroll(0);
        $this->expectException(SessionFullException::class);
        $s->assertCanEnroll(1);
    }
}
