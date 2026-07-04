<?php

namespace App\Modules\Leave\Infrastructure\Http\Controllers\Actions;

use App\Modules\Leave\Application\QueryHandlers\GetLeaveRequestHandler;
use App\Modules\Leave\Infrastructure\Http\Controllers\LeaveRequestController;

class ShowLeaveRequestController
{
    public function __construct(private LeaveRequestController $controller) {}

    public function __invoke(string $id, GetLeaveRequestHandler $handler)
    {
        return $this->controller->show($id, $handler);
    }
}
