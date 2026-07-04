<?php

namespace App\Modules\Offboarding\Infrastructure\Http\Controllers\Actions;

use App\Modules\Offboarding\Infrastructure\Http\Controllers\OffboardingPlanController;
use Illuminate\Http\Request;

class ListOffboardingPlanController
{
    public function __construct(private OffboardingPlanController $controller) {}

    public function __invoke(Request $request)
    {
        return $this->controller->index($request);
    }
}
