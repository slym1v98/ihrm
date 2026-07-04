<?php

namespace App\Modules\Payroll\Infrastructure\Http\Controllers\Actions;

use App\Modules\Payroll\Infrastructure\Http\Controllers\PayrollEntryController;
use Illuminate\Http\Request;

class ReviewPayrollEntryController
{
    public function __construct(private PayrollEntryController $controller) {}

    public function __invoke(Request $request, string $id)
    {
        return $this->controller->review($request, $id);
    }
}
