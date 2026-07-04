<?php

namespace App\Modules\Organization\Infrastructure\Http\Controllers\Actions;

use App\Modules\Organization\Infrastructure\Http\Controllers\PositionController;

class ShowPositionController
{
    public function __construct(private PositionController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->show($id);
    }
}
