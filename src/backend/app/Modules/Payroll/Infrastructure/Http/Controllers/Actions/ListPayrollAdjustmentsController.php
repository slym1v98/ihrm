<?php

namespace App\Modules\Payroll\Infrastructure\Http\Controllers\Actions;

use App\Modules\Payroll\Infrastructure\Http\Controllers\PayrollAdjustmentController;
use Illuminate\Http\Request;

class ListPayrollAdjustmentsController
{
    public function __construct(private PayrollAdjustmentController $controller) {}

    public function __invoke(Request $request, string $entryId)
    {
        return $this->controller->index($request, $entryId);
    }
}
