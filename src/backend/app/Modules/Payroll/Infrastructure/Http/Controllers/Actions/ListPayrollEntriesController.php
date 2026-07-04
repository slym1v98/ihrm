<?php

namespace App\Modules\Payroll\Infrastructure\Http\Controllers\Actions;

use App\Modules\Payroll\Infrastructure\Http\Controllers\PayrollEntryController;
use Illuminate\Http\Request;

class ListPayrollEntriesController
{
    public function __construct(private PayrollEntryController $controller) {}

    public function __invoke(Request $request, string $periodId)
    {
        return $this->controller->index($request, $periodId);
    }
}
