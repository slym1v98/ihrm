<?php

namespace App\Modules\Payroll\Infrastructure\Http\Controllers\Actions;

use App\Modules\Payroll\Infrastructure\Http\Controllers\PayrollSummaryController;

class ShowPayrollPeriodSummaryController
{
    public function __construct(private PayrollSummaryController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->show($id);
    }
}
