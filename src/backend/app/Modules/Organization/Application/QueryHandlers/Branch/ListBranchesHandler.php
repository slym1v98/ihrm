<?php

namespace App\Modules\Organization\Application\QueryHandlers\Branch;

use App\Modules\Organization\Application\Queries\Branch\ListBranchesQuery;
use App\Modules\Organization\Infrastructure\Persistence\Eloquent\BranchModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListBranchesHandler
{
    public function handle(ListBranchesQuery $query): LengthAwarePaginator
    {
        return BranchModel::query()
            ->when($query->status, fn ($builder) => $builder->where('status', $query->status))
            ->orderBy('name')
            ->paginate($query->perPage, ['*'], 'page', $query->page);
    }
}
