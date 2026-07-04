<?php

namespace App\Modules\Attendance\Infrastructure\Http\Controllers\Actions;

use App\Modules\Attendance\Infrastructure\Http\Controllers\AttendancePeriodController;
use App\Modules\Attendance\Infrastructure\Http\Requests\OpenAttendancePeriodRequest;

class StoreAttendancePeriodController
{
    public function __construct(private AttendancePeriodController $controller) {}

    public function __invoke(OpenAttendancePeriodRequest $request)
    {
        return $this->controller->store($request);
    }
}
