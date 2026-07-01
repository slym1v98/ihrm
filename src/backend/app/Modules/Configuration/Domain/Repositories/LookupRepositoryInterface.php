<?php

namespace App\Modules\Configuration\Domain\Repositories;

use App\Modules\Configuration\Infrastructure\Persistence\Eloquent\LookupGroupModel;
use App\Modules\Configuration\Infrastructure\Persistence\Eloquent\LookupValueModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface LookupRepositoryInterface
{
    public function listGroups(int $perPage = 20): LengthAwarePaginator;
    public function findGroup(string $id): ?LookupGroupModel;
    public function findGroupByCode(string $code): ?LookupGroupModel;
    public function saveGroup(array $attributes): LookupGroupModel;
    public function saveValue(LookupGroupModel $group, array $attributes): LookupValueModel;
}
