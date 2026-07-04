<?php

namespace App\Modules\Attendance\Infrastructure\Http\Controllers\Actions;

use App\Modules\Attendance\Infrastructure\Http\Controllers\AttendancePeriodController;
use Illuminate\Http\Request;

class ListAttendancePeriodController
{
    public function __construct(private AttendancePeriodController $controller) {}

    public function __invoke(Request $request)
    {
        return $this->controller->index($request);
    }
}
