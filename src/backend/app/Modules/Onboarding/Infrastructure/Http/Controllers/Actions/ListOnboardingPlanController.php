<?php

namespace App\Modules\Onboarding\Infrastructure\Http\Controllers\Actions;

use App\Modules\Onboarding\Infrastructure\Http\Controllers\OnboardingPlanController;
use Illuminate\Http\Request;

class ListOnboardingPlanController
{
    public function __construct(private OnboardingPlanController $controller) {}

    public function __invoke(Request $request)
    {
        return $this->controller->index($request);
    }
}
