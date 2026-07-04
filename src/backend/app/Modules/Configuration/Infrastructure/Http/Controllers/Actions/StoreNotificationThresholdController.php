<?php

namespace App\Modules\Configuration\Infrastructure\Http\Controllers\Actions;

use App\Modules\Configuration\Domain\Repositories\NotificationThresholdRepositoryInterface;
use App\Modules\Configuration\Infrastructure\Http\Controllers\NotificationThresholdController;
use App\Modules\Configuration\Infrastructure\Http\Requests\StoreNotificationThresholdRequest;

class StoreNotificationThresholdController
{
    public function __construct(private NotificationThresholdController $controller) {}

    public function __invoke(StoreNotificationThresholdRequest $request, NotificationThresholdRepositoryInterface $thresholds)
    {
        return $this->controller->store($request, $thresholds);
    }
}
