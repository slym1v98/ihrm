<?php

namespace App\Modules\Leave\Infrastructure\Http\Controllers\Actions;

use App\Modules\Leave\Application\CommandHandlers\LeaveRequest\RejectLeaveRequestHandler;
use App\Modules\Leave\Infrastructure\Http\Controllers\LeaveRequestController;
use App\Modules\Leave\Infrastructure\Http\Requests\RejectLeaveRequest;

class RejectLeaveRequestController
{
    public function __construct(private LeaveRequestController $controller) {}

    public function __invoke(string $id, RejectLeaveRequest $req, RejectLeaveRequestHandler $handler)
    {
        return $this->controller->reject($id, $req, $handler);
    }
}
