<?php

namespace App\Modules\Organization\Infrastructure\Http\Controllers\Actions;

use App\Modules\Organization\Infrastructure\Http\Controllers\DepartmentController;
use App\Modules\Organization\Infrastructure\Http\Requests\UpdateDepartmentRequest;

class UpdateDepartmentController
{
    public function __construct(private DepartmentController $controller) {}

    public function __invoke(UpdateDepartmentRequest $request, string $id)
    {
        return $this->controller->update($request, $id);
    }
}
