<?php

namespace App\Modules\Payroll\Infrastructure\Http\Controllers\Actions;

use App\Modules\Payroll\Infrastructure\Http\Controllers\PayrollRunController;
use Illuminate\Http\Request;

class StartPayrollRunController
{
    public function __construct(private PayrollRunController $controller) {}

    public function __invoke(Request $request, string $periodId)
    {
        return $this->controller->start($request, $periodId);
    }
}
