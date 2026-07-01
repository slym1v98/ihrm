<?php

namespace App\Modules\Organization\Domain\Repositories;

use App\Modules\Organization\Domain\Aggregates\Branch\Branch;
use App\Modules\Organization\Domain\Aggregates\Branch\BranchCode;
use App\Modules\Organization\Domain\Aggregates\Branch\BranchId;

interface BranchRepositoryInterface
{
    public function findById(BranchId $id): Branch;

    public function findByCode(BranchCode $code): ?Branch;

    public function existsByCode(BranchCode $code): bool;

    public function hasActiveDepartments(BranchId $id): bool;

    public function save(Branch $branch): void;

    public function saveAndDispatch(Branch $branch): void;
}
