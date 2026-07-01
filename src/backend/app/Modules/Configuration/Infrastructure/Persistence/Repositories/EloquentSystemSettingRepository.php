<?php

namespace App\Modules\Configuration\Infrastructure\Persistence\Repositories;

use App\Modules\Configuration\Domain\Repositories\SystemSettingRepositoryInterface;
use App\Modules\Configuration\Infrastructure\Persistence\Eloquent\SystemSettingModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentSystemSettingRepository implements SystemSettingRepositoryInterface
{
    public function list(int $perPage = 50): LengthAwarePaginator { return SystemSettingModel::orderBy('key')->paginate($perPage); }
    public function findByKey(string $key): ?SystemSettingModel { return SystemSettingModel::where('key', $key)->first(); }
    public function save(array $attributes): SystemSettingModel { return SystemSettingModel::updateOrCreate(['key' => $attributes['key']], $attributes); }
}
