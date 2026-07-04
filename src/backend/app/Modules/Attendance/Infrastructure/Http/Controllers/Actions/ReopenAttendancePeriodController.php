<?php

namespace App\Modules\Attendance\Infrastructure\Http\Controllers\Actions;

use App\Modules\Attendance\Infrastructure\Http\Controllers\AttendancePeriodController;
use App\Modules\Attendance\Infrastructure\Http\Requests\ReopenAttendancePeriodRequest;

class ReopenAttendancePeriodController
{
    public function __construct(private AttendancePeriodController $controller) {}

    public function __invoke(string $id, ReopenAttendancePeriodRequest $request)
    {
        return $this->controller->reopen($id, $request);
    }
}
