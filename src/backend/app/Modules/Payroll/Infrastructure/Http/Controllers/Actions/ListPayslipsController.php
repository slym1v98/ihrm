<?php

namespace App\Modules\Payroll\Infrastructure\Http\Controllers\Actions;

use App\Modules\Payroll\Infrastructure\Http\Controllers\PayslipController;
use Illuminate\Http\Request;

class ListPayslipsController
{
    public function __construct(private PayslipController $controller) {}

    public function __invoke(Request $request)
    {
        return $this->controller->index($request);
    }
}
