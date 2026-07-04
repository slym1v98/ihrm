<?php

namespace App\Modules\Shift\Infrastructure\Http\Controllers\Actions;

use App\Modules\Shift\Infrastructure\Http\Controllers\ShiftAssignmentController;
use Illuminate\Http\Request;

class StoreShiftAssignmentController
{
    public function __construct(private ShiftAssignmentController $controller) {}

    public function __invoke(Request $request)
    {
        return $this->controller->store($request);
    }
}
