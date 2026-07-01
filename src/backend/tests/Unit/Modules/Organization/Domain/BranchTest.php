<?php

namespace Tests\Unit\Modules\Organization\Domain;

use App\Modules\Organization\Domain\Aggregates\Branch\Branch;
use App\Modules\Organization\Domain\Aggregates\Branch\BranchCode;
use App\Modules\Organization\Domain\Aggregates\Branch\BranchId;
use App\Modules\Organization\Domain\Aggregates\Branch\BranchName;
use App\Modules\Organization\Domain\Aggregates\Branch\BranchStatus;
use App\Modules\Organization\Domain\Events\BranchActivated;
use App\Modules\Organization\Domain\Events\BranchCreated;
use App\Modules\Organization\Domain\Events\BranchDeactivated;
use App\Modules\Organization\Domain\Events\BranchUpdated;
use App\Modules\Organization\Domain\Exceptions\BranchHasActiveDepartmentsException;
use PHPUnit\Framework\TestCase;

class BranchTest extends TestCase
{
    private Branch $branch;

    protected function setUp(): void
    {
        $this->branch = Branch::create(
            BranchId::fromString('550e8400-e29b-41d4-a716-446655440000'),
            BranchCode::fromString('HCM-HQ'),
            BranchName::fromString('Ho Chi Minh HQ'),
            '123 Nguyen Hue', '0909123456', 'hcm@example.com',
        );
    }

    public function test_create_emits_branch_created_event(): void
    {
        $events = $this->branch->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(BranchCreated::class, $events[0]);
        $this->assertSame('HCM-HQ', $events[0]->code);
    }

    public function test_update_emits_branch_updated_event(): void
    {
        $this->branch->releaseEvents();
        $this->branch->update(BranchName::fromString('Updated'), null, null, null);
        $events = $this->branch->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(BranchUpdated::class, $events[0]);
        $this->assertSame('Updated', $this->branch->name()->value);
    }

    public function test_activate_idempotent_when_already_active(): void
    {
        $this->branch->releaseEvents();
        $this->branch->activate();
        $events = $this->branch->releaseEvents();
        $this->assertEmpty($events);
    }

    public function test_deactivate_emits_event_when_no_departments(): void
    {
        $this->branch->releaseEvents();
        $this->branch->deactivate(fn () => false);
        $events = $this->branch->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(BranchDeactivated::class, $events[0]);
        $this->assertSame(BranchStatus::Inactive, $this->branch->status());
    }

    public function test_deactivate_throws_when_has_active_departments(): void
    {
        $this->branch->releaseEvents();
        $this->expectException(BranchHasActiveDepartmentsException::class);
        $this->branch->deactivate(fn () => true);
    }

    public function test_reconstitute_does_not_emit_events(): void
    {
        $branch = Branch::reconstitute(
            BranchId::fromString('550e8400-e29b-41d4-a716-446655440000'),
            BranchCode::fromString('HCM-HQ'),
            BranchName::fromString('Ho Chi Minh HQ'),
            null, null, null,
            BranchStatus::Active,
        );
        $this->assertEmpty($branch->releaseEvents());
    }

    public function test_reactivate_after_deactivate(): void
    {
        $this->branch->deactivate(fn () => false);
        $this->branch->releaseEvents();
        $this->branch->activate();
        $events = $this->branch->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(BranchActivated::class, $events[0]);
        $this->assertSame(BranchStatus::Active, $this->branch->status());
    }
}
