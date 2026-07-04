<?php

namespace App\Modules\Payroll\Infrastructure\Http\Controllers\Actions;

use App\Modules\Payroll\Infrastructure\Http\Controllers\PayrollAdjustmentController;
use Illuminate\Http\Request;

class ApprovePayrollAdjustmentController
{
    public function __construct(private PayrollAdjustmentController $controller) {}

    public function __invoke(Request $request, string $id)
    {
        return $this->controller->approve($request, $id);
    }
}
