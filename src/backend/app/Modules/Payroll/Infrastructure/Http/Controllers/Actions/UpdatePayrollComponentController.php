<?php

namespace App\Modules\Payroll\Infrastructure\Http\Controllers\Actions;

use App\Modules\Payroll\Infrastructure\Http\Controllers\PayrollComponentController;
use App\Modules\Payroll\Infrastructure\Http\Requests\UpdatePayrollComponentRequest;

class UpdatePayrollComponentController
{
    public function __construct(private PayrollComponentController $controller) {}

    public function __invoke(UpdatePayrollComponentRequest $request, string $id)
    {
        return $this->controller->update($request, $id);
    }
}
