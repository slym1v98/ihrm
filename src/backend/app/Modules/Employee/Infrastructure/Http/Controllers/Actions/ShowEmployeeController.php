<?php

namespace App\Modules\Employee\Infrastructure\Http\Controllers\Actions;

use App\Modules\Employee\Infrastructure\Http\Controllers\EmployeeController;

class ShowEmployeeController
{
    public function __construct(private EmployeeController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->show($id);
    }
}
