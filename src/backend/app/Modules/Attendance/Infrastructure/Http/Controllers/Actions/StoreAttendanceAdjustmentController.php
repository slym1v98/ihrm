<?php

namespace App\Modules\Attendance\Infrastructure\Http\Controllers\Actions;

use App\Modules\Attendance\Infrastructure\Http\Controllers\AttendanceAdjustmentController;
use App\Modules\Attendance\Infrastructure\Http\Requests\SubmitAttendanceAdjustmentRequest;

class StoreAttendanceAdjustmentController
{
    public function __construct(private AttendanceAdjustmentController $controller) {}

    public function __invoke(SubmitAttendanceAdjustmentRequest $request)
    {
        return $this->controller->store($request);
    }
}
