<?php

namespace App\Modules\Leave\Infrastructure\Http\Controllers\Actions;

use App\Modules\Leave\Application\CommandHandlers\LeaveRequest\SubmitLeaveRequestHandler;
use App\Modules\Leave\Infrastructure\Http\Controllers\LeaveRequestController;
use App\Modules\Leave\Infrastructure\Http\Requests\SubmitLeaveRequest;

class StoreLeaveRequestController
{
    public function __construct(private LeaveRequestController $controller) {}

    public function __invoke(SubmitLeaveRequest $req, SubmitLeaveRequestHandler $handler)
    {
        return $this->controller->store($req, $handler);
    }
}
