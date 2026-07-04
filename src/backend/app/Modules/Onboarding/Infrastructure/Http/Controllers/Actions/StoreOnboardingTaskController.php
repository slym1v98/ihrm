<?php

namespace App\Modules\Onboarding\Infrastructure\Http\Controllers\Actions;

use App\Modules\Onboarding\Infrastructure\Http\Controllers\OnboardingTaskController;
use Illuminate\Http\Request;

class StoreOnboardingTaskController
{
    public function __construct(private OnboardingTaskController $controller) {}

    public function __invoke(Request $request, string $planId)
    {
        return $this->controller->store($request, $planId);
    }
}
