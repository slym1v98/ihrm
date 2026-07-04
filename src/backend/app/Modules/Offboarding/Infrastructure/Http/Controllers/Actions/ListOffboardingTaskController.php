<?php

namespace App\Modules\Offboarding\Infrastructure\Http\Controllers\Actions;

use App\Modules\Offboarding\Infrastructure\Http\Controllers\OffboardingTaskController;

class ListOffboardingTaskController
{
    public function __construct(private OffboardingTaskController $controller) {}

    public function __invoke(string $planId)
    {
        return $this->controller->index($planId);
    }
}
