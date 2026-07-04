<?php

namespace App\Modules\Offboarding\Infrastructure\Http\Controllers\Actions;

use App\Modules\Offboarding\Infrastructure\Http\Controllers\OffboardingRequestController;
use Illuminate\Http\Request;

class ListOffboardingRequestController
{
    public function __construct(private OffboardingRequestController $controller) {}

    public function __invoke(Request $request)
    {
        return $this->controller->index($request);
    }
}
