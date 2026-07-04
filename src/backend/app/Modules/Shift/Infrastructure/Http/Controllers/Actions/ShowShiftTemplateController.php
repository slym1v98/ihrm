<?php

namespace App\Modules\Shift\Infrastructure\Http\Controllers\Actions;

use App\Modules\Shift\Infrastructure\Http\Controllers\ShiftTemplateController;

class ShowShiftTemplateController
{
    public function __construct(private ShiftTemplateController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->show($id);
    }
}
