<?php

namespace App\Modules\Leave\Infrastructure\Http\Controllers\Actions;

use App\Modules\Leave\Application\CommandHandlers\LeaveRequest\ApproveLeaveRequestHandler;
use App\Modules\Leave\Infrastructure\Http\Controllers\LeaveRequestController;
use App\Modules\Leave\Infrastructure\Http\Requests\ApproveLeaveRequest;

class ApproveLeaveRequestController
{
    public function __construct(private LeaveRequestController $controller) {}

    public function __invoke(string $id, ApproveLeaveRequest $req, ApproveLeaveRequestHandler $handler)
    {
        return $this->controller->approve($id, $req, $handler);
    }
}
