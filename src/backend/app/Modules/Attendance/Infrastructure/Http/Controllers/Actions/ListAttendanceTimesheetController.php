<?php

namespace App\Modules\Attendance\Infrastructure\Http\Controllers\Actions;

use App\Modules\Attendance\Infrastructure\Http\Controllers\AttendanceTimesheetController;
use Illuminate\Http\Request;

class ListAttendanceTimesheetController
{
    public function __construct(private AttendanceTimesheetController $controller) {}

    public function __invoke(Request $request)
    {
        return $this->controller->index($request);
    }
}
