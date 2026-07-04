<?php

namespace App\Modules\Leave\Infrastructure\Http\Controllers\Actions;

use App\Modules\Leave\Infrastructure\Http\Controllers\LeaveTypeController;

class ListLeaveTypeController
{
    public function __construct(private LeaveTypeController $controller) {}

    public function __invoke()
    {
        return $this->controller->index();
    }
}
