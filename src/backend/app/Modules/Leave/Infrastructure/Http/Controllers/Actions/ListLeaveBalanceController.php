<?php

namespace App\Modules\Leave\Infrastructure\Http\Controllers\Actions;

use App\Modules\Leave\Application\QueryHandlers\GetEmployeeLeaveBalanceHandler;
use App\Modules\Leave\Infrastructure\Http\Controllers\LeaveBalanceController;

class ListLeaveBalanceController
{
    public function __construct(private LeaveBalanceController $controller) {}

    public function __invoke(GetEmployeeLeaveBalanceHandler $handler)
    {
        return $this->controller->index($handler);
    }
}
