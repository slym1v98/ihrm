<?php

namespace App\Modules\Shift\Infrastructure\Http\Controllers\Actions;

use App\Modules\Shift\Infrastructure\Http\Controllers\ShiftAssignmentController;
use Illuminate\Http\Request;

class UpdateShiftAssignmentController
{
    public function __construct(private ShiftAssignmentController $controller) {}

    public function __invoke(Request $request, string $id)
    {
        return $this->controller->update($request, $id);
    }
}
