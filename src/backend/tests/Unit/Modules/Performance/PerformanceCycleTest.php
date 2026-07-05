<?php

namespace Tests\Unit\Modules\Performance;

use App\Modules\Performance\Domain\Aggregates\PerformanceCycle\PerformanceCycle;
use App\Modules\Performance\Domain\Aggregates\PerformanceCycle\PerformanceCycleId;
use App\Modules\Performance\Domain\Exceptions\InvalidStatusTransitionException;
use App\Modules\Performance\Domain\ValueObjects\CycleStatus;
use PHPUnit\Framework\TestCase;

class PerformanceCycleTest extends TestCase
{
    public function test_cycle_lifecycle_draft_active_completed(): void
    {
        $c = PerformanceCycle::create(PerformanceCycleId::generate(), 'C1', 'Cycle 1', null,
            new \DateTimeImmutable('2026-01-01'), new \DateTimeImmutable('2026-03-31'), []);
        $this->assertSame(CycleStatus::Draft, $c->getStatus());
        $c->activate();
        $this->assertSame(CycleStatus::Active, $c->getStatus());
        $c->complete();
        $this->assertSame(CycleStatus::Completed, $c->getStatus());
    }

    public function test_completed_cycle_cannot_be_activated(): void
    {
        $c = PerformanceCycle::create(PerformanceCycleId::generate(), 'C2', 'Cycle 2', null,
            new \DateTimeImmutable('2026-01-01'), new \DateTimeImmutable('2026-03-31'), []);
        $c->activate();
        $c->complete();
        $this->expectException(InvalidStatusTransitionException::class);
        $c->activate();
    }

    public function test_start_after_end_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PerformanceCycle::create(PerformanceCycleId::generate(), 'C3', 'Cycle 3', null,
            new \DateTimeImmutable('2026-03-31'), new \DateTimeImmutable('2026-01-01'), []);
    }
}
