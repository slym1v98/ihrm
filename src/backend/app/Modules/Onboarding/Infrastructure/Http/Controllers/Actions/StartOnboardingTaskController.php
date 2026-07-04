<?php

namespace App\Modules\Onboarding\Infrastructure\Http\Controllers\Actions;

use App\Modules\Onboarding\Infrastructure\Http\Controllers\OnboardingTaskController;

class StartOnboardingTaskController
{
    public function __construct(private OnboardingTaskController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->start($id);
    }
}
