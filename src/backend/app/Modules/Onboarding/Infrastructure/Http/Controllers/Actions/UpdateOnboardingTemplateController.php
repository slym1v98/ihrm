<?php

namespace App\Modules\Onboarding\Infrastructure\Http\Controllers\Actions;

use App\Modules\Onboarding\Infrastructure\Http\Controllers\OnboardingTemplateController;
use Illuminate\Http\Request;

class UpdateOnboardingTemplateController
{
    public function __construct(private OnboardingTemplateController $controller) {}

    public function __invoke(Request $request, string $id)
    {
        return $this->controller->update($request, $id);
    }
}
