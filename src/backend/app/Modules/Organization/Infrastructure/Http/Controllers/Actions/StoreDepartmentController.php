<?php

namespace App\Modules\Organization\Infrastructure\Http\Controllers\Actions;

use App\Modules\Organization\Infrastructure\Http\Controllers\DepartmentController;
use App\Modules\Organization\Infrastructure\Http\Requests\CreateDepartmentRequest;

class StoreDepartmentController
{
    public function __construct(private DepartmentController $controller) {}

    public function __invoke(CreateDepartmentRequest $request)
    {
        return $this->controller->store($request);
    }
}
