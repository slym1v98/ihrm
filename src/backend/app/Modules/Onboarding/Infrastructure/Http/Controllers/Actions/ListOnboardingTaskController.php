<?php

namespace App\Modules\Onboarding\Infrastructure\Http\Controllers\Actions;

use App\Modules\Onboarding\Infrastructure\Http\Controllers\OnboardingTaskController;

class ListOnboardingTaskController
{
    public function __construct(private OnboardingTaskController $controller) {}

    public function __invoke(string $planId)
    {
        return $this->controller->index($planId);
    }
}
