<?php

namespace App\Modules\Performance\Infrastructure\Http\Controllers\Actions;

use App\Modules\Performance\Infrastructure\Http\Controllers\CompetencyTemplateController;

class ListCompetencyTemplateController
{
    public function __construct(private CompetencyTemplateController $controller) {}

    public function __invoke()
    {
        return $this->controller->index();
    }
}
