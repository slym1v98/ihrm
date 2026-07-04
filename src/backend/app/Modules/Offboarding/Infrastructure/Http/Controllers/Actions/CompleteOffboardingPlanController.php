<?php

namespace App\Modules\Offboarding\Infrastructure\Http\Controllers\Actions;

use App\Modules\Offboarding\Infrastructure\Http\Controllers\OffboardingPlanController;
use Illuminate\Http\Request;

class CompleteOffboardingPlanController
{
    public function __construct(private OffboardingPlanController $controller) {}

    public function __invoke(Request $request, string $id)
    {
        return $this->controller->complete($request, $id);
    }
}
