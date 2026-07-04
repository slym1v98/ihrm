<?php

namespace App\Modules\Offboarding\Infrastructure\Http\Controllers\Actions;

use App\Modules\Offboarding\Infrastructure\Http\Controllers\OffboardingTaskController;

class StartOffboardingTaskController
{
    public function __construct(private OffboardingTaskController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->start($id);
    }
}
