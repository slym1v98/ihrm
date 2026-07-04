<?php

namespace App\Modules\Offboarding\Infrastructure\Http\Controllers\Actions;

use App\Modules\Offboarding\Infrastructure\Http\Controllers\OffboardingRequestController;

class SubmitOffboardingRequestController
{
    public function __construct(private OffboardingRequestController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->submit($id);
    }
}
