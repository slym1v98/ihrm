<?php

namespace App\Modules\Employee\Infrastructure\Http\Controllers\Actions;

use App\Modules\Employee\Infrastructure\Http\Controllers\EmployeeDocumentController;
use Illuminate\Http\Request;

class StoreEmployeeDocumentController
{
    public function __construct(private EmployeeDocumentController $controller) {}

    public function __invoke(Request $request, string $employeeId)
    {
        return $this->controller->store($request, $employeeId);
    }
}
