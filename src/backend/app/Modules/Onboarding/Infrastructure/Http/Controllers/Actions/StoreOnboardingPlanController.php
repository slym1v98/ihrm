<?php

namespace App\Modules\Onboarding\Infrastructure\Http\Controllers\Actions;

use App\Modules\Onboarding\Infrastructure\Http\Controllers\OnboardingPlanController;
use Illuminate\Http\Request;

class StoreOnboardingPlanController
{
    public function __construct(private OnboardingPlanController $controller) {}

    public function __invoke(Request $request)
    {
        return $this->controller->store($request);
    }
}
