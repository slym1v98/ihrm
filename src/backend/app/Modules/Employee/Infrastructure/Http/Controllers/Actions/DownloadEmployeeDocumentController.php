<?php

namespace App\Modules\Employee\Infrastructure\Http\Controllers\Actions;

use App\Modules\Employee\Infrastructure\Http\Controllers\EmployeeDocumentController;

class DownloadEmployeeDocumentController
{
    public function __construct(private EmployeeDocumentController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->download($id);
    }
}
