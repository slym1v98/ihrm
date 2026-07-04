<?php

namespace App\Modules\Payroll\Infrastructure\Http\Controllers\Actions;

use App\Modules\Payroll\Infrastructure\Http\Controllers\PayrollAdjustmentController;
use App\Modules\Payroll\Infrastructure\Http\Requests\SubmitPayrollAdjustmentRequest;

class StorePayrollAdjustmentController
{
    public function __construct(private PayrollAdjustmentController $controller) {}

    public function __invoke(SubmitPayrollAdjustmentRequest $request, string $entryId)
    {
        return $this->controller->store($request, $entryId);
    }
}
