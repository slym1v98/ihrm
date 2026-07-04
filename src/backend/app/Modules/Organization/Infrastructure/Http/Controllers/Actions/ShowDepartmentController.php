<?php

namespace App\Modules\Organization\Infrastructure\Http\Controllers\Actions;

use App\Modules\Organization\Infrastructure\Http\Controllers\DepartmentController;

class ShowDepartmentController
{
    public function __construct(private DepartmentController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->show($id);
    }
}
