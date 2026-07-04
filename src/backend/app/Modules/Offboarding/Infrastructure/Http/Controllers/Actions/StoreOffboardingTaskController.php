<?php

namespace App\Modules\Offboarding\Infrastructure\Http\Controllers\Actions;

use App\Modules\Offboarding\Infrastructure\Http\Controllers\OffboardingTaskController;
use Illuminate\Http\Request;

class StoreOffboardingTaskController
{
    public function __construct(private OffboardingTaskController $controller) {}

    public function __invoke(Request $request, string $planId)
    {
        return $this->controller->store($request, $planId);
    }
}
