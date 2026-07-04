<?php

namespace App\Modules\Organization\Infrastructure\Http\Controllers\Actions;

use App\Modules\Organization\Infrastructure\Http\Controllers\PositionController;
use Illuminate\Http\Request;

class ActivatePositionController
{
    public function __construct(private PositionController $controller) {}

    public function __invoke(Request $request, string $id)
    {
        return $this->controller->activate($request, $id);
    }
}
