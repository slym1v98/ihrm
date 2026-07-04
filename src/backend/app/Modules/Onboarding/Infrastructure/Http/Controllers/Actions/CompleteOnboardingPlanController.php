<?php

namespace App\Modules\Onboarding\Infrastructure\Http\Controllers\Actions;

use App\Modules\Onboarding\Infrastructure\Http\Controllers\OnboardingPlanController;
use Illuminate\Http\Request;

class CompleteOnboardingPlanController
{
    public function __construct(private OnboardingPlanController $controller) {}

    public function __invoke(Request $request, string $id)
    {
        return $this->controller->complete($request, $id);
    }
}
