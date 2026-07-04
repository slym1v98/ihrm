<?php

namespace App\Modules\Attendance\Infrastructure\Http\Controllers\Actions;

use App\Modules\Attendance\Infrastructure\Http\Controllers\AttendanceRawLogController;
use App\Modules\Attendance\Infrastructure\Http\Requests\RecordAttendanceRawLogRequest;

class StoreAttendanceRawLogController
{
    public function __construct(private AttendanceRawLogController $controller) {}

    public function __invoke(RecordAttendanceRawLogRequest $request)
    {
        return $this->controller->store($request);
    }
}
