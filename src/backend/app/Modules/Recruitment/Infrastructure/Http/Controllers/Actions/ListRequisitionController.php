<?php

namespace App\Modules\Recruitment\Infrastructure\Http\Controllers\Actions;

use App\Modules\Recruitment\Infrastructure\Http\Controllers\RequisitionController;

class ListRequisitionController
{
    public function __construct(private RequisitionController $controller) {}

    public function __invoke()
    {
        return $this->controller->index();
    }
}
