<?php

namespace App\Modules\Attendance\Infrastructure\Http\Controllers\Actions;

use App\Modules\Attendance\Infrastructure\Http\Controllers\AttendanceAdjustmentController;
use Illuminate\Http\Request;

class ApproveAttendanceAdjustmentController
{
    public function __construct(private AttendanceAdjustmentController $controller) {}

    public function __invoke(string $id, Request $request)
    {
        return $this->controller->approve($id, $request);
    }
}
