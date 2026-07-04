<?php

namespace App\Modules\Offboarding\Infrastructure\Http\Controllers\Actions;

use App\Modules\Offboarding\Infrastructure\Http\Controllers\OffboardingRequestController;
use Illuminate\Http\Request;

class ApproveOffboardingRequestController
{
    public function __construct(private OffboardingRequestController $controller) {}

    public function __invoke(Request $request, string $id)
    {
        return $this->controller->approve($request, $id);
    }
}
