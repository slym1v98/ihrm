<?php

namespace App\Modules\Performance\Infrastructure\Http\Controllers\Actions;

use App\Modules\Performance\Infrastructure\Http\Controllers\CompetencyTemplateController;
use Illuminate\Http\Request;

class UpdateCompetencyTemplateController
{
    public function __construct(private CompetencyTemplateController $controller) {}

    public function __invoke(Request $r, string $id)
    {
        return $this->controller->update($r, $id);
    }
}
