<?php

namespace App\Modules\Attendance\Infrastructure\Http\Controllers\Actions;

use App\Modules\Attendance\Infrastructure\Http\Controllers\AttendanceTimesheetController;
use App\Modules\Attendance\Infrastructure\Http\Requests\CalculateAttendanceRequest;

class CalculateAttendanceTimesheetController
{
    public function __construct(private AttendanceTimesheetController $controller) {}

    public function __invoke(CalculateAttendanceRequest $request)
    {
        return $this->controller->calculate($request);
    }
}
