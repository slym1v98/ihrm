<?php

namespace App\Modules\Organization\Domain\Repositories;

use App\Modules\Organization\Domain\Aggregates\Branch\BranchId;
use App\Modules\Organization\Domain\Aggregates\Department\Department;
use App\Modules\Organization\Domain\Aggregates\Department\DepartmentCode;
use App\Modules\Organization\Domain\Aggregates\Department\DepartmentId;

interface DepartmentRepositoryInterface
{
    public function findById(DepartmentId $id): Department;

    public function findByCodeAndBranch(DepartmentCode $code, BranchId $branchId): ?Department;

    public function existsByCodeAndBranch(DepartmentCode $code, BranchId $branchId): bool;

    /** @return Department[] */
    public function findChildrenOf(DepartmentId $id): array;

    public function hasActiveChildren(DepartmentId $id): bool;

    /** @return string[] */
    public function findDescendantIds(DepartmentId $id): array;

    public function findBranchIdOf(DepartmentId $id): BranchId;

    public function save(Department $department): void;

    public function saveAndDispatch(Department $department): void;
}
