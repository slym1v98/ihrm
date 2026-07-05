<?php

namespace Tests\Unit\Modules\Organization\Domain;

use App\Modules\Organization\Domain\Aggregates\Branch\BranchId;
use App\Modules\Organization\Domain\Aggregates\Department\Department;
use App\Modules\Organization\Domain\Aggregates\Department\DepartmentCode;
use App\Modules\Organization\Domain\Aggregates\Department\DepartmentId;
use App\Modules\Organization\Domain\Aggregates\Department\DepartmentName;
use App\Modules\Organization\Domain\Aggregates\Department\DepartmentStatus;
use App\Modules\Organization\Domain\Events\DepartmentCreated;
use App\Modules\Organization\Domain\Events\DepartmentDeactivated;
use App\Modules\Organization\Domain\Events\DepartmentMoved;
use App\Modules\Organization\Domain\Exceptions\CircularMoveException;
use App\Modules\Organization\Domain\Exceptions\DepartmentHasActiveChildrenException;
use App\Modules\Organization\Domain\Exceptions\DepartmentNotInSameBranchException;
use PHPUnit\Framework\TestCase;

class DepartmentTest extends TestCase
{
    private BranchId $branchId;

    private DepartmentId $parentId;

    private DepartmentId $childId;

    protected function setUp(): void
    {
        $this->branchId = BranchId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $this->parentId = DepartmentId::fromString('660e8400-e29b-41d4-a716-446655440001');
        $this->childId = DepartmentId::fromString('660e8400-e29b-41d4-a716-446655440002');
    }

    private function makeDept(DepartmentId $id, string $code = 'IT', ?DepartmentId $parentId = null): Department
    {
        return Department::create(
            $id,
            DepartmentCode::fromString($code),
            DepartmentName::fromString($code.' Dept'),
            $this->branchId,
            $parentId,
        );
    }

    public function test_create_emits_department_created_event(): void
    {
        $dept = $this->makeDept($this->parentId);
        $events = $dept->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(DepartmentCreated::class, $events[0]);
    }

    public function test_move_to_self_throws_circular_move(): void
    {
        $dept = $this->makeDept($this->parentId);
        $dept->releaseEvents();
        $this->expectException(CircularMoveException::class);
        $dept->moveTo($dept->id(), fn ($id) => false, fn ($id) => $this->branchId);
    }

    public function test_move_to_descendant_throws_circular_move(): void
    {
        $parent = $this->makeDept($this->parentId, 'IT');
        $parent->releaseEvents();
        $this->expectException(CircularMoveException::class);
        $parent->moveTo($this->childId, fn ($id) => true, fn ($id) => $this->branchId);
    }

    public function test_move_to_different_branch_throws(): void
    {
        $dept = $this->makeDept($this->childId, 'DEV');
        $dept->releaseEvents();
        $otherBranch = BranchId::fromString('770e8400-e29b-41d4-a716-446655440099');
        $this->expectException(DepartmentNotInSameBranchException::class);
        $dept->moveTo(
            $this->parentId,
            fn ($id) => false,
            fn ($id) => $otherBranch,
        );
    }

    public function test_move_to_valid_parent_emits_moved_event(): void
    {
        $dept = $this->makeDept($this->childId, 'DEV');
        $dept->releaseEvents();
        $dept->moveTo($this->parentId, fn ($id) => false, fn ($id) => $this->branchId);
        $events = $dept->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(DepartmentMoved::class, $events[0]);
        $this->assertTrue($dept->parentId()->equals($this->parentId));
    }

    public function test_move_to_null_removes_parent(): void
    {
        $dept = $this->makeDept($this->childId, 'DEV', $this->parentId);
        $dept->releaseEvents();
        $dept->moveTo(null, fn ($id) => false, fn ($id) => $this->branchId);
        $this->assertNull($dept->parentId());
    }

    public function test_deactivate_with_children_throws(): void
    {
        $dept = $this->makeDept($this->parentId);
        $dept->releaseEvents();
        $this->expectException(DepartmentHasActiveChildrenException::class);
        $dept->deactivate(fn () => true);
    }

    public function test_deactivate_without_children_emits_event(): void
    {
        $dept = $this->makeDept($this->parentId);
        $dept->releaseEvents();
        $dept->deactivate(fn () => false);
        $events = $dept->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(DepartmentDeactivated::class, $events[0]);
        $this->assertSame(DepartmentStatus::Inactive, $dept->status());
    }

    public function test_reconstitute_does_not_emit_events(): void
    {
        $dept = Department::reconstitute(
            $this->parentId,
            DepartmentCode::fromString('IT'),
            DepartmentName::fromString('IT Dept'),
            $this->branchId,
            null, null,
            DepartmentStatus::Active,
        );
        $this->assertEmpty($dept->releaseEvents());
    }
}
