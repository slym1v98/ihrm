<?php

namespace App\Modules\Organization\Infrastructure\Http\Controllers\Actions;

use App\Modules\Organization\Infrastructure\Http\Controllers\PositionController;
use Illuminate\Http\Request;

class ListPositionController
{
    public function __construct(private PositionController $controller) {}

    public function __invoke(Request $request)
    {
        return $this->controller->index($request);
    }
}
