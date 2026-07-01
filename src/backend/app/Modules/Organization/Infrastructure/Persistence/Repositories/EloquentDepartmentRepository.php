<?php

namespace App\Modules\Organization\Infrastructure\Persistence\Repositories;

use App\Modules\Organization\Domain\Aggregates\Branch\BranchId;
use App\Modules\Organization\Domain\Aggregates\Department\Department;
use App\Modules\Organization\Domain\Aggregates\Department\DepartmentCode;
use App\Modules\Organization\Domain\Aggregates\Department\DepartmentId;
use App\Modules\Organization\Domain\Aggregates\Department\DepartmentName;
use App\Modules\Organization\Domain\Aggregates\Department\DepartmentStatus;
use App\Modules\Organization\Domain\Exceptions\DepartmentNotFoundException;
use App\Modules\Organization\Domain\Repositories\DepartmentRepositoryInterface;
use App\Modules\Organization\Infrastructure\Persistence\Eloquent\DepartmentModel;
use Illuminate\Support\Facades\Event;

class EloquentDepartmentRepository implements DepartmentRepositoryInterface
{
    public function __construct(private DepartmentModel $model) {}

    public function findById(DepartmentId $id): Department
    {
        $record = $this->model->with('branch')->find($id->value);
        if (!$record) throw new DepartmentNotFoundException($id->value);
        return $this->toDomain($record);
    }

    public function findByCodeAndBranch(DepartmentCode $code, BranchId $branchId): ?Department
    {
        $record = $this->model->where('code', $code->value)->where('branch_id', $branchId->value)->first();
        return $record ? $this->toDomain($record) : null;
    }

    public function existsByCodeAndBranch(DepartmentCode $code, BranchId $branchId): bool
    {
        return $this->model->where('code', $code->value)->where('branch_id', $branchId->value)->exists();
    }

    public function findChildrenOf(DepartmentId $id): array
    {
        return $this->model->where('parent_id', $id->value)->get()->map(fn($r) => $this->toDomain($r))->all();
    }

    public function hasActiveChildren(DepartmentId $id): bool
    {
        return $this->model->where('parent_id', $id->value)->where('status', 'active')->exists();
    }

    public function findDescendantIds(DepartmentId $id): array
    {
        $ids = [];
        $this->collectDescendantIds($id->value, $ids);
        return $ids;
    }

    private function collectDescendantIds(string $parentId, array &$ids): void
    {
        foreach ($this->model->where('parent_id', $parentId)->pluck('id') as $childId) {
            $ids[] = $childId;
            $this->collectDescendantIds($childId, $ids);
        }
    }

    public function findBranchIdOf(DepartmentId $id): BranchId
    {
        $record = $this->model->find($id->value);
        if (!$record) throw new DepartmentNotFoundException($id->value);
        return BranchId::fromString($record->branch_id);
    }

    public function save(Department $department): void
    {
        $this->model->updateOrCreate(
            ['id' => $department->id()->value],
            [
                'branch_id' => $department->branchId()->value,
                'parent_id' => $department->parentId()?->value,
                'code' => $department->code()->value,
                'name' => $department->name()->value,
                'manager_employee_id' => $department->managerEmployeeId(),
                'status' => $department->status()->value,
            ]
        );
    }

    public function saveAndDispatch(Department $department): void
    {
        $this->save($department);
        foreach ($department->releaseEvents() as $event) {
            Event::dispatch($event);
        }
    }

    private function toDomain(DepartmentModel $record): Department
    {
        return Department::reconstitute(
            DepartmentId::fromString($record->id),
            DepartmentCode::fromString($record->code),
            DepartmentName::fromString($record->name),
            BranchId::fromString($record->branch_id),
            $record->parent_id ? DepartmentId::fromString($record->parent_id) : null,
            $record->manager_employee_id,
            DepartmentStatus::from($record->status),
        );
    }
}
