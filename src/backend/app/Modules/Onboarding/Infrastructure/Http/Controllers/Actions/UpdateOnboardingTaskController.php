<?php

namespace App\Modules\Onboarding\Infrastructure\Http\Controllers\Actions;

use App\Modules\Onboarding\Infrastructure\Http\Controllers\OnboardingTaskController;
use Illuminate\Http\Request;

class UpdateOnboardingTaskController
{
    public function __construct(private OnboardingTaskController $controller) {}

    public function __invoke(Request $request, string $id)
    {
        return $this->controller->update($request, $id);
    }
}
