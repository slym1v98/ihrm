<?php

namespace App\Modules\Leave\Infrastructure\Http\Controllers\Actions;

use App\Modules\Leave\Infrastructure\Http\Controllers\LeaveBalanceController;

class SummaryLeaveBalanceController
{
    public function __construct(private LeaveBalanceController $controller) {}

    public function __invoke()
    {
        return $this->controller->summary();
    }
}
