<?php

namespace App\Modules\Onboarding\Infrastructure\Http\Controllers\Actions;

use App\Modules\Onboarding\Infrastructure\Http\Controllers\OnboardingPlanController;

class ActivateOnboardingPlanController
{
    public function __construct(private OnboardingPlanController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->activate($id);
    }
}
