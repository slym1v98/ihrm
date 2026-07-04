<?php

namespace App\Modules\Notification\Infrastructure\Http\Controllers\Actions;

use App\Modules\Notification\Infrastructure\Http\Controllers\NotificationController;
use Illuminate\Http\Request;

class UnreadCountNotificationController
{
    public function __construct(private NotificationController $controller) {}

    public function __invoke(Request $request)
    {
        return $this->controller->unreadCount($request);
    }
}
