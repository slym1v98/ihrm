<?php

namespace App\Modules\Onboarding\Infrastructure\Http\Controllers\Actions;

use App\Modules\Onboarding\Infrastructure\Http\Controllers\OnboardingPlanController;

class CancelOnboardingPlanController
{
    public function __construct(private OnboardingPlanController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->cancel($id);
    }
}
