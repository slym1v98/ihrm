<?php

namespace App\Modules\Reporting\Infrastructure\Http\Controllers\Actions;

use App\Modules\Reporting\Infrastructure\Http\Controllers\ReportController;
use Illuminate\Http\Request;

class ShowRunReportController
{
    public function __construct(private ReportController $controller) {}

    public function __invoke(Request $request, string $id)
    {
        return $this->controller->showRun($request, $id);
    }
}
