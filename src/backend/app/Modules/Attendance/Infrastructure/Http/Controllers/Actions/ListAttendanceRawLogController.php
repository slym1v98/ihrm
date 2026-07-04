<?php

namespace App\Modules\Attendance\Infrastructure\Http\Controllers\Actions;

use App\Modules\Attendance\Infrastructure\Http\Controllers\AttendanceRawLogController;
use Illuminate\Http\Request;

class ListAttendanceRawLogController
{
    public function __construct(private AttendanceRawLogController $controller) {}

    public function __invoke(Request $request)
    {
        return $this->controller->index($request);
    }
}
