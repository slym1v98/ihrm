<?php

namespace Tests\Unit\Modules\Organization\Domain;

use App\Modules\Organization\Domain\Aggregates\Position\Position;
use App\Modules\Organization\Domain\Aggregates\Position\PositionCode;
use App\Modules\Organization\Domain\Aggregates\Position\PositionId;
use App\Modules\Organization\Domain\Aggregates\Position\PositionName;
use App\Modules\Organization\Domain\Aggregates\Position\PositionStatus;
use App\Modules\Organization\Domain\Events\PositionCreated;
use App\Modules\Organization\Domain\Events\PositionDeactivated;
use App\Modules\Organization\Domain\Events\PositionUpdated;
use PHPUnit\Framework\TestCase;

class PositionTest extends TestCase
{
    private Position $position;

    protected function setUp(): void
    {
        $this->position = Position::create(
            PositionId::fromString('880e8400-e29b-41d4-a716-446655440000'),
            PositionCode::fromString('DEV'),
            PositionName::fromString('Developer'),
            3,
        );
    }

    public function test_create_emits_position_created_event(): void
    {
        $events = $this->position->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(PositionCreated::class, $events[0]);
        $this->assertSame('DEV', $events[0]->code);
    }

    public function test_update_changes_name_and_emits_event(): void
    {
        $this->position->releaseEvents();
        $this->position->update(PositionName::fromString('Senior Developer'), 4, 'Senior role');
        $events = $this->position->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(PositionUpdated::class, $events[0]);
        $this->assertSame('Senior Developer', $this->position->name()->value);
        $this->assertSame(4, $this->position->level());
    }

    public function test_deactivate_emits_event(): void
    {
        $this->position->releaseEvents();
        $this->position->deactivate();
        $events = $this->position->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(PositionDeactivated::class, $events[0]);
        $this->assertSame(PositionStatus::Inactive, $this->position->status());
    }

    public function test_deactivate_idempotent_when_already_inactive(): void
    {
        $this->position->deactivate();
        $this->position->releaseEvents();
        $this->position->deactivate();
        $this->assertEmpty($this->position->releaseEvents());
    }

    public function test_reconstitute_does_not_emit_events(): void
    {
        $pos = Position::reconstitute(
            PositionId::fromString('880e8400-e29b-41d4-a716-446655440001'),
            PositionCode::fromString('SR-DEV'),
            PositionName::fromString('Senior Developer'),
            4, null,
            PositionStatus::Active,
        );
        $this->assertEmpty($pos->releaseEvents());
    }
}
