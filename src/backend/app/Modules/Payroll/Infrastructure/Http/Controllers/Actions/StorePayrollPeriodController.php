<?php

namespace App\Modules\Payroll\Infrastructure\Http\Controllers\Actions;

use App\Modules\Payroll\Infrastructure\Http\Controllers\PayrollPeriodController;
use App\Modules\Payroll\Infrastructure\Http\Requests\StorePayrollPeriodRequest;

class StorePayrollPeriodController
{
    public function __construct(private PayrollPeriodController $controller) {}

    public function __invoke(StorePayrollPeriodRequest $request)
    {
        return $this->controller->store($request);
    }
}
