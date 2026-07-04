<?php

namespace App\Modules\Leave\Infrastructure\Http\Controllers\Actions;

use App\Modules\Leave\Application\CommandHandlers\LeaveRequest\CancelLeaveRequestHandler;
use App\Modules\Leave\Infrastructure\Http\Controllers\LeaveRequestController;
use App\Modules\Leave\Infrastructure\Http\Requests\CancelLeaveRequest;

class CancelLeaveRequestController
{
    public function __construct(private LeaveRequestController $controller) {}

    public function __invoke(string $id, CancelLeaveRequest $req, CancelLeaveRequestHandler $handler)
    {
        return $this->controller->cancel($id, $req, $handler);
    }
}
