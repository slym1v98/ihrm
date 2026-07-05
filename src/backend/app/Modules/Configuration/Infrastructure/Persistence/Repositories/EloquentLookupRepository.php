<?php

namespace App\Modules\Configuration\Infrastructure\Persistence\Repositories;

use App\Modules\Configuration\Domain\Repositories\LookupRepositoryInterface;
use App\Modules\Configuration\Infrastructure\Persistence\Eloquent\LookupGroupModel;
use App\Modules\Configuration\Infrastructure\Persistence\Eloquent\LookupValueModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentLookupRepository implements LookupRepositoryInterface
{
    public function listGroups(int $perPage = 20): LengthAwarePaginator
    {
        return LookupGroupModel::with('values')->orderBy('code')->paginate($perPage);
    }

    public function findGroup(string $id): ?LookupGroupModel
    {
        return LookupGroupModel::with('values')->find($id);
    }

    public function findGroupByCode(string $code): ?LookupGroupModel
    {
        return LookupGroupModel::with('values')->where('code', $code)->first();
    }

    public function saveGroup(array $attributes): LookupGroupModel
    {
        return LookupGroupModel::updateOrCreate(['id' => $attributes['id'] ?? null], $attributes);
    }

    public function saveValue(LookupGroupModel $group, array $attributes): LookupValueModel
    {
        return $group->values()->updateOrCreate(['id' => $attributes['id'] ?? null], $attributes);
    }
}
