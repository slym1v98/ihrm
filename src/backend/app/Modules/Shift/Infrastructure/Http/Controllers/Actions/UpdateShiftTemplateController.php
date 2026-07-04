<?php

namespace App\Modules\Shift\Infrastructure\Http\Controllers\Actions;

use App\Modules\Shift\Infrastructure\Http\Controllers\ShiftTemplateController;
use Illuminate\Http\Request;

class UpdateShiftTemplateController
{
    public function __construct(private ShiftTemplateController $controller) {}

    public function __invoke(Request $request, string $id)
    {
        return $this->controller->update($request, $id);
    }
}
