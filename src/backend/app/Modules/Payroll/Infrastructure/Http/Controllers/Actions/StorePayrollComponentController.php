<?php

namespace App\Modules\Payroll\Infrastructure\Http\Controllers\Actions;

use App\Modules\Payroll\Infrastructure\Http\Controllers\PayrollComponentController;
use App\Modules\Payroll\Infrastructure\Http\Requests\StorePayrollComponentRequest;

class StorePayrollComponentController
{
    public function __construct(private PayrollComponentController $controller) {}

    public function __invoke(StorePayrollComponentRequest $request)
    {
        return $this->controller->store($request);
    }
}
