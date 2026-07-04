<?php

namespace App\Modules\Notification\Infrastructure\Http\Controllers\Actions;

use App\Modules\Notification\Infrastructure\Http\Controllers\NotificationController;
use Illuminate\Http\Request;

class MarkReadNotificationController
{
    public function __construct(private NotificationController $controller) {}

    public function __invoke(Request $request, string $id)
    {
        return $this->controller->markRead($request, $id);
    }
}
