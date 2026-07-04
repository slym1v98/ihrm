<?php

namespace App\Modules\Organization\Infrastructure\Http\Controllers\Actions;

use App\Modules\Organization\Infrastructure\Http\Controllers\DepartmentController;
use Illuminate\Http\Request;

class ListDepartmentController
{
    public function __construct(private DepartmentController $controller) {}

    public function __invoke(Request $request)
    {
        return $this->controller->index($request);
    }
}
