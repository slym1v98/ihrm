<?php

namespace App\Modules\Payroll\Infrastructure\Http\Controllers\Actions;

use App\Modules\Payroll\Infrastructure\Http\Controllers\PayrollComponentController;

class DeletePayrollComponentController
{
    public function __construct(private PayrollComponentController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->destroy($id);
    }
}
