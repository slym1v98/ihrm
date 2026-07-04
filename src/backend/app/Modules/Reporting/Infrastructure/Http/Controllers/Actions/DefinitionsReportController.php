<?php

namespace App\Modules\Reporting\Infrastructure\Http\Controllers\Actions;

use App\Modules\Reporting\Infrastructure\Http\Controllers\ReportController;

class DefinitionsReportController
{
    public function __construct(private ReportController $controller) {}

    public function __invoke()
    {
        return $this->controller->definitions();
    }
}
