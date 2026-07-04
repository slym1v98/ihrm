<?php

namespace App\Modules\Attendance\Infrastructure\Http\Controllers\Actions;

use App\Modules\Attendance\Infrastructure\Http\Controllers\AttendanceTimesheetController;
use Illuminate\Http\Request;

class EmployeeAttendanceAttendanceTimesheetController
{
    public function __construct(private AttendanceTimesheetController $controller) {}

    public function __invoke(string $id, Request $request)
    {
        return $this->controller->employeeAttendance($id, $request);
    }
}
