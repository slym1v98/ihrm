<?php

namespace App\Modules\Payroll\Infrastructure\Http\Controllers\Actions;

use App\Modules\Payroll\Infrastructure\Http\Controllers\PayrollPeriodController;
use Illuminate\Http\Request;

class SubmitPayrollPeriodApprovalController
{
    public function __construct(private PayrollPeriodController $controller) {}

    public function __invoke(Request $request, string $id)
    {
        return $this->controller->submitApproval($request, $id);
    }
}
