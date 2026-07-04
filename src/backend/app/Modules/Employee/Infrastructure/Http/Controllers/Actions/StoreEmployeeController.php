<?php

namespace App\Modules\Employee\Infrastructure\Http\Controllers\Actions;

use App\Modules\Employee\Infrastructure\Http\Controllers\EmployeeController;
use App\Modules\Employee\Infrastructure\Http\Requests\CreateEmployeeRequest;

class StoreEmployeeController
{
    public function __construct(private EmployeeController $controller) {}

    public function __invoke(CreateEmployeeRequest $request)
    {
        return $this->controller->store($request);
    }
}
