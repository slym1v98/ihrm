<?php

namespace App\Modules\Configuration\Infrastructure\Persistence\Repositories;

use App\Modules\Configuration\Domain\Repositories\NotificationThresholdRepositoryInterface;
use App\Modules\Configuration\Infrastructure\Persistence\Eloquent\NotificationThresholdModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentNotificationThresholdRepository implements NotificationThresholdRepositoryInterface
{
    public function list(int $perPage = 20): LengthAwarePaginator
    {
        return NotificationThresholdModel::orderBy('code')->paginate($perPage);
    }

    public function find(string $id): ?NotificationThresholdModel
    {
        return NotificationThresholdModel::find($id);
    }

    public function findByCode(string $code): ?NotificationThresholdModel
    {
        return NotificationThresholdModel::where('code', $code)->first();
    }

    public function save(array $attributes): NotificationThresholdModel
    {
        return NotificationThresholdModel::updateOrCreate(['id' => $attributes['id'] ?? null], $attributes);
    }
}
