<?php

namespace App\Modules\Organization\Infrastructure\Http\Controllers\Actions;

use App\Modules\Organization\Infrastructure\Http\Controllers\DepartmentController;
use App\Modules\Organization\Infrastructure\Http\Requests\MoveDepartmentRequest;

class MoveDepartmentController
{
    public function __construct(private DepartmentController $controller) {}

    public function __invoke(MoveDepartmentRequest $request, string $id)
    {
        return $this->controller->move($request, $id);
    }
}
