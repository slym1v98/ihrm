<?php

namespace App\Modules\Configuration\Domain\Repositories;

use App\Modules\Configuration\Infrastructure\Persistence\Eloquent\SystemSettingModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SystemSettingRepositoryInterface
{
    public function list(int $perPage = 50): LengthAwarePaginator;

    public function findByKey(string $key): ?SystemSettingModel;

    public function save(array $attributes): SystemSettingModel;
}
