<?php

namespace App\Modules\Payroll\Infrastructure\Http\Controllers\Actions;

use App\Modules\Payroll\Infrastructure\Http\Controllers\PayrollEntryController;

class ShowPayrollEntryController
{
    public function __construct(private PayrollEntryController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->show($id);
    }
}
