<?php

namespace App\Modules\Organization\Application\QueryHandlers\Department;

use App\Modules\Organization\Application\Queries\Department\ListDepartmentsQuery;
use App\Modules\Organization\Infrastructure\Persistence\Eloquent\DepartmentModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListDepartmentsHandler
{
    public function handle(ListDepartmentsQuery $query): LengthAwarePaginator
    {
        return DepartmentModel::query()
            ->with(['branch', 'parent'])
            ->when($query->branchId, fn ($builder) => $builder->where('branch_id', $query->branchId))
            ->when($query->parentId, fn ($builder) => $builder->where('parent_id', $query->parentId))
            ->when($query->status, fn ($builder) => $builder->where('status', $query->status))
            ->orderBy('name')
            ->paginate($query->perPage, ['*'], 'page', $query->page);
    }
}
