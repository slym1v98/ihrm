<?php

namespace App\Modules\Organization\Infrastructure\Http\Controllers\Actions;

use App\Modules\Organization\Infrastructure\Http\Controllers\PositionController;
use App\Modules\Organization\Infrastructure\Http\Requests\UpdatePositionRequest;

class UpdatePositionController
{
    public function __construct(private PositionController $controller) {}

    public function __invoke(UpdatePositionRequest $request, string $id)
    {
        return $this->controller->update($request, $id);
    }
}
