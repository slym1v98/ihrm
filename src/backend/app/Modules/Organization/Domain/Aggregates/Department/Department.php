<?php

namespace App\Modules\Organization\Domain\Aggregates\Department;

use App\Modules\Organization\Domain\Aggregates\Branch\BranchId;
use App\Modules\Organization\Domain\Events\DepartmentActivated;
use App\Modules\Organization\Domain\Events\DepartmentCreated;
use App\Modules\Organization\Domain\Events\DepartmentDeactivated;
use App\Modules\Organization\Domain\Events\DepartmentMoved;
use App\Modules\Organization\Domain\Events\DepartmentUpdated;
use App\Modules\Organization\Domain\Exceptions\CircularMoveException;
use App\Modules\Organization\Domain\Exceptions\DepartmentHasActiveChildrenException;
use App\Modules\Organization\Domain\Exceptions\DepartmentNotInSameBranchException;
use DateTimeImmutable;

final class Department
{
    /** @var object[] */
    private array $recordedEvents = [];

    private function __construct(
        private readonly DepartmentId $id,
        private readonly DepartmentCode $code,
        private DepartmentName $name,
        private readonly BranchId $branchId,
        private ?DepartmentId $parentId,
        private ?string $managerEmployeeId,
        private DepartmentStatus $status,
    ) {}

    public static function create(
        DepartmentId $id,
        DepartmentCode $code,
        DepartmentName $name,
        BranchId $branchId,
        ?DepartmentId $parentId = null,
    ): self {
        $dept = new self($id, $code, $name, $branchId, $parentId, null, DepartmentStatus::Active);
        $dept->record(new DepartmentCreated($id, $code->value, $name->value, new DateTimeImmutable));

        return $dept;
    }

    public static function reconstitute(
        DepartmentId $id,
        DepartmentCode $code,
        DepartmentName $name,
        BranchId $branchId,
        ?DepartmentId $parentId,
        ?string $managerEmployeeId,
        DepartmentStatus $status,
    ): self {
        return new self($id, $code, $name, $branchId, $parentId, $managerEmployeeId, $status);
    }

    public function update(DepartmentName $name, ?string $managerEmployeeId): void
    {
        $this->name = $name;
        $this->managerEmployeeId = $managerEmployeeId;
        $this->record(new DepartmentUpdated($this->id, new DateTimeImmutable));
    }

    /**
     * @param  callable(?DepartmentId): bool  $isDescendantFn  returns true if given id is a descendant
     * @param  callable(DepartmentId): BranchId  $getParentBranchFn  returns branch of given dept
     */
    public function moveTo(
        ?DepartmentId $newParentId,
        callable $isDescendantFn,
        callable $getParentBranchFn,
    ): void {
        if ($newParentId !== null && $this->id->equals($newParentId)) {
            throw new CircularMoveException;
        }

        if ($newParentId !== null && $isDescendantFn($newParentId)) {
            throw new CircularMoveException;
        }

        if ($newParentId !== null) {
            $parentBranchId = $getParentBranchFn($newParentId);
            if (! $parentBranchId->equals($this->branchId)) {
                throw new DepartmentNotInSameBranchException;
            }
        }

        $oldParentId = $this->parentId;
        $this->parentId = $newParentId;
        $this->record(new DepartmentMoved($this->id, $oldParentId, $newParentId, new DateTimeImmutable));
    }

    public function activate(): void
    {
        if ($this->status->isActive()) {
            return;
        }
        $this->status = DepartmentStatus::Active;
        $this->record(new DepartmentActivated($this->id, new DateTimeImmutable));
    }

    /** @param callable(): bool $hasActiveChildrenFn */
    public function deactivate(callable $hasActiveChildrenFn): void
    {
        if ($this->status->isInactive()) {
            return;
        }
        if ($hasActiveChildrenFn()) {
            throw new DepartmentHasActiveChildrenException($this->id->value);
        }
        $this->status = DepartmentStatus::Inactive;
        $this->record(new DepartmentDeactivated($this->id, new DateTimeImmutable));
    }

    private function record(object $event): void
    {
        $this->recordedEvents[] = $event;
    }

    /** @return object[] */
    public function releaseEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];

        return $events;
    }

    public function id(): DepartmentId
    {
        return $this->id;
    }

    public function code(): DepartmentCode
    {
        return $this->code;
    }

    public function name(): DepartmentName
    {
        return $this->name;
    }

    public function branchId(): BranchId
    {
        return $this->branchId;
    }

    public function parentId(): ?DepartmentId
    {
        return $this->parentId;
    }

    public function managerEmployeeId(): ?string
    {
        return $this->managerEmployeeId;
    }

    public function status(): DepartmentStatus
    {
        return $this->status;
    }
}
