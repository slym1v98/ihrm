<?php

namespace App\Modules\Notification\Infrastructure\Http\Controllers\Actions;

use App\Modules\Notification\Infrastructure\Http\Controllers\NotificationPreferenceController;
use Illuminate\Http\Request;

class ListNotificationPreferenceController
{
    public function __construct(private NotificationPreferenceController $controller) {}

    public function __invoke(Request $request)
    {
        return $this->controller->index($request);
    }
}
