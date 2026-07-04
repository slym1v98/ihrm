<?php

namespace App\Modules\Attendance\Infrastructure\Http\Controllers\Actions;

use App\Modules\Attendance\Infrastructure\Http\Controllers\AttendancePeriodController;

class CloseAttendancePeriodController
{
    public function __construct(private AttendancePeriodController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->close($id);
    }
}
