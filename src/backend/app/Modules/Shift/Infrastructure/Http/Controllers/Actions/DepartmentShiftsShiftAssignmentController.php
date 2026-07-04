<?php

namespace App\Modules\Shift\Infrastructure\Http\Controllers\Actions;

use App\Modules\Shift\Infrastructure\Http\Controllers\ShiftAssignmentController;
use Illuminate\Http\Request;

class DepartmentShiftsShiftAssignmentController
{
    public function __construct(private ShiftAssignmentController $controller) {}

    public function __invoke(string $id, Request $request)
    {
        return $this->controller->departmentShifts($id, $request);
    }
}
