<?php

namespace App\Modules\Recruitment\Infrastructure\Http\Controllers\Actions;

use App\Modules\Recruitment\Infrastructure\Http\Controllers\RequisitionController;
use Illuminate\Http\Request;

class StoreRequisitionController
{
    public function __construct(private RequisitionController $controller) {}

    public function __invoke(Request $r)
    {
        return $this->controller->store($r);
    }
}
