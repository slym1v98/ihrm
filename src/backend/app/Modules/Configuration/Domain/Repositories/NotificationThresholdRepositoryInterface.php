<?php

namespace App\Modules\Configuration\Domain\Repositories;

use App\Modules\Configuration\Infrastructure\Persistence\Eloquent\NotificationThresholdModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface NotificationThresholdRepositoryInterface
{
    public function list(int $perPage = 20): LengthAwarePaginator;
    public function find(string $id): ?NotificationThresholdModel;
    public function findByCode(string $code): ?NotificationThresholdModel;
    public function save(array $attributes): NotificationThresholdModel;
}
