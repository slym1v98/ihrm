<?php

namespace App\Modules\Shift\Infrastructure\Http\Controllers\Actions;

use App\Modules\Shift\Infrastructure\Http\Controllers\ShiftTemplateController;
use Illuminate\Http\Request;

class ListShiftTemplateController
{
    public function __construct(private ShiftTemplateController $controller) {}

    public function __invoke(Request $request)
    {
        return $this->controller->index($request);
    }
}
