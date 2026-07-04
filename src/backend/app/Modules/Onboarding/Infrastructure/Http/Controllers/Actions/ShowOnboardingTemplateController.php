<?php

namespace App\Modules\Onboarding\Infrastructure\Http\Controllers\Actions;

use App\Modules\Onboarding\Infrastructure\Http\Controllers\OnboardingTemplateController;

class ShowOnboardingTemplateController
{
    public function __construct(private OnboardingTemplateController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->show($id);
    }
}
