<?php

namespace App\Modules\Organization\Application\QueryHandlers\Position;

use App\Modules\Organization\Application\Queries\Position\ListPositionsQuery;
use App\Modules\Organization\Infrastructure\Persistence\Eloquent\PositionModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListPositionsHandler
{
    public function handle(ListPositionsQuery $query): LengthAwarePaginator
    {
        return PositionModel::query()
            ->when($query->status, fn ($builder) => $builder->where('status', $query->status))
            ->orderBy('name')
            ->paginate($query->perPage, ['*'], 'page', $query->page);
    }
}
