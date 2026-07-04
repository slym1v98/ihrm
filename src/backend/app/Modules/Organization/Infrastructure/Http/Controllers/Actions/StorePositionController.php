<?php

namespace App\Modules\Organization\Infrastructure\Http\Controllers\Actions;

use App\Modules\Organization\Infrastructure\Http\Controllers\PositionController;
use App\Modules\Organization\Infrastructure\Http\Requests\CreatePositionRequest;

class StorePositionController
{
    public function __construct(private PositionController $controller) {}

    public function __invoke(CreatePositionRequest $request)
    {
        return $this->controller->store($request);
    }
}
