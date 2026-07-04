<?php

namespace App\Modules\Employee\Infrastructure\Http\Controllers\Actions;

use App\Modules\Employee\Infrastructure\Http\Controllers\EmployeeController;
use Illuminate\Http\Request;

class ChangeStatusEmployeeController
{
    public function __construct(private EmployeeController $controller) {}

    public function __invoke(Request $request, string $id)
    {
        return $this->controller->changeStatus($request, $id);
    }
}
