<?php

namespace App\Modules\Payroll\Infrastructure\Http\Controllers\Actions;

use App\Modules\Payroll\Infrastructure\Http\Controllers\PayrollComponentController;
use Illuminate\Http\Request;

class ListPayrollComponentsController
{
    public function __construct(private PayrollComponentController $controller) {}

    public function __invoke(Request $request)
    {
        return $this->controller->index($request);
    }
}
