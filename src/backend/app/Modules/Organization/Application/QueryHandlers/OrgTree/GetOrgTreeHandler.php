<?php

namespace App\Modules\Organization\Application\QueryHandlers\OrgTree;

use App\Modules\Organization\Application\Queries\OrgTree\GetOrgTreeQuery;
use App\Modules\Organization\Infrastructure\Persistence\Eloquent\BranchModel;
use App\Modules\Organization\Infrastructure\Persistence\Eloquent\DepartmentModel;

class GetOrgTreeHandler
{
    public function handle(GetOrgTreeQuery $query): array
    {
        $branches = BranchModel::query()
            ->when($query->branchId, fn ($builder) => $builder->where('id', $query->branchId))
            ->orderBy('name')
            ->get();

        $branchIds = $branches->pluck('id')->all();
        $departments = DepartmentModel::query()
            ->whereIn('branch_id', $branchIds)
            ->orderBy('name')
            ->get();

        $departmentsByBranch = $departments->groupBy('branch_id');

        return $branches->map(function (BranchModel $branch) use ($departmentsByBranch) {
            $branchDepartments = $departmentsByBranch->get($branch->id, collect());

            $tree = $this->buildDepartmentTree($branchDepartments->all(), null);

            return [
                'id' => $branch->id,
                'code' => $branch->code,
                'name' => $branch->name,
                'departments' => $tree,
            ];
        })->all();
    }

    private function buildDepartmentTree(array $departments, ?string $parentId): array
    {
        $nodes = [];

        foreach ($departments as $department) {
            if ($department->parent_id !== $parentId) {
                continue;
            }

            $nodes[] = [
                'id' => $department->id,
                'code' => $department->code,
                'name' => $department->name,
                'branch_id' => $department->branch_id,
                'parent_id' => $department->parent_id,
                'children' => $this->buildDepartmentTree($departments, $department->id),
            ];
        }

        return $nodes;
    }
}
