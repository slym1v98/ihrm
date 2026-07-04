<?php

namespace App\Modules\Onboarding\Infrastructure\Http\Controllers\Actions;

use App\Modules\Onboarding\Infrastructure\Http\Controllers\OnboardingTemplateController;
use Illuminate\Http\Request;

class StoreOnboardingTemplateController
{
    public function __construct(private OnboardingTemplateController $controller) {}

    public function __invoke(Request $request)
    {
        return $this->controller->store($request);
    }
}
