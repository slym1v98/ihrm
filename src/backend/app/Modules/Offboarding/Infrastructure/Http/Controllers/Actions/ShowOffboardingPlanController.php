<?php

namespace App\Modules\Offboarding\Infrastructure\Http\Controllers\Actions;

use App\Modules\Offboarding\Infrastructure\Http\Controllers\OffboardingPlanController;

class ShowOffboardingPlanController
{
    public function __construct(private OffboardingPlanController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->show($id);
    }
}
