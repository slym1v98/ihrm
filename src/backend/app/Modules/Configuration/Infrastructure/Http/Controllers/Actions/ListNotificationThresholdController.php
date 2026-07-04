<?php

namespace App\Modules\Configuration\Infrastructure\Http\Controllers\Actions;

use App\Modules\Configuration\Domain\Repositories\NotificationThresholdRepositoryInterface;
use App\Modules\Configuration\Infrastructure\Http\Controllers\NotificationThresholdController;
use Illuminate\Http\Request;

class ListNotificationThresholdController
{
    public function __construct(private NotificationThresholdController $controller) {}

    public function __invoke(Request $request, NotificationThresholdRepositoryInterface $thresholds)
    {
        return $this->controller->index($request, $thresholds);
    }
}
