<?php

namespace App\Modules\Employee\Infrastructure\Http\Controllers\Actions;

use App\Modules\Employee\Infrastructure\Http\Controllers\EmployeeController;
use Illuminate\Http\Request;

class ListEmployeeController
{
    public function __construct(private EmployeeController $controller) {}

    public function __invoke(Request $request)
    {
        return $this->controller->index($request);
    }
}
