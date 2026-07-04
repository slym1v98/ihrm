<?php

namespace App\Modules\Onboarding\Infrastructure\Http\Controllers\Actions;

use App\Modules\Onboarding\Infrastructure\Http\Controllers\OnboardingPlanController;

class ShowOnboardingPlanController
{
    public function __construct(private OnboardingPlanController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->show($id);
    }
}
