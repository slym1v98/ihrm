<?php

namespace App\Modules\Payroll\Infrastructure\Http\Controllers\Actions;

use App\Modules\Payroll\Infrastructure\Http\Controllers\PayrollPeriodController;
use Illuminate\Http\Request;

class ListPayrollPeriodsController
{
    public function __construct(private PayrollPeriodController $controller) {}

    public function __invoke(Request $request)
    {
        return $this->controller->index($request);
    }
}
