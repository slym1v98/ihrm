<?php

namespace App\Modules\Payroll\Infrastructure\Http\Controllers\Actions;

use App\Modules\Payroll\Infrastructure\Http\Controllers\PayrollPeriodController;

class ShowPayrollPeriodController
{
    public function __construct(private PayrollPeriodController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->show($id);
    }
}
