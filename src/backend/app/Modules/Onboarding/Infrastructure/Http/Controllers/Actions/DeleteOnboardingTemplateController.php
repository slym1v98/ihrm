<?php

namespace App\Modules\Onboarding\Infrastructure\Http\Controllers\Actions;

use App\Modules\Onboarding\Infrastructure\Http\Controllers\OnboardingTemplateController;

class DeleteOnboardingTemplateController
{
    public function __construct(private OnboardingTemplateController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->destroy($id);
    }
}
