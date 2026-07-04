<?php

namespace App\Modules\Performance\Infrastructure\Http\Controllers\Actions;

use App\Modules\Performance\Infrastructure\Http\Controllers\CompetencyTemplateController;
use Illuminate\Http\Request;

class StoreCompetencyTemplateController
{
    public function __construct(private CompetencyTemplateController $controller) {}

    public function __invoke(Request $r)
    {
        return $this->controller->store($r);
    }
}
