<?php

namespace App\Modules\Payroll\Infrastructure\Http\Controllers\Actions;

use App\Modules\Payroll\Infrastructure\Http\Controllers\PayslipController;

class ShowPayslipController
{
    public function __construct(private PayslipController $controller) {}

    public function __invoke(\Illuminate\Http\Request $request, string $id)
    {
        return $this->controller->show($request, $id);
    }
}
