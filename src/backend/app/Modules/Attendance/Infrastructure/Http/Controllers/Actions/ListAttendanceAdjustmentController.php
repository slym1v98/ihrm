<?php

namespace App\Modules\Attendance\Infrastructure\Http\Controllers\Actions;

use App\Modules\Attendance\Infrastructure\Http\Controllers\AttendanceAdjustmentController;
use Illuminate\Http\Request;

class ListAttendanceAdjustmentController
{
    public function __construct(private AttendanceAdjustmentController $controller) {}

    public function __invoke(Request $request)
    {
        return $this->controller->index($request);
    }
}
