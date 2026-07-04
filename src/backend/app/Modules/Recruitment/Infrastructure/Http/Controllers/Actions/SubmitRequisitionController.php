<?php

namespace App\Modules\Recruitment\Infrastructure\Http\Controllers\Actions;

use App\Modules\Recruitment\Infrastructure\Http\Controllers\RequisitionController;
use Illuminate\Http\Request;

class SubmitRequisitionController
{
    public function __construct(private RequisitionController $controller) {}

    public function __invoke(Request $r, string $id)
    {
        return $this->controller->submit($r, $id);
    }
}
