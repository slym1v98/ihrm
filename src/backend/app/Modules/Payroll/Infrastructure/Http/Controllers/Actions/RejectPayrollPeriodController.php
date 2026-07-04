<?php

namespace App\Modules\Payroll\Infrastructure\Http\Controllers\Actions;

use App\Modules\Payroll\Infrastructure\Http\Controllers\PayrollPeriodController;
use Illuminate\Http\Request;

class RejectPayrollPeriodController
{
    public function __construct(private PayrollPeriodController $controller) {}

    public function __invoke(Request $request, string $id)
    {
        return $this->controller->reject($request, $id);
    }
}
