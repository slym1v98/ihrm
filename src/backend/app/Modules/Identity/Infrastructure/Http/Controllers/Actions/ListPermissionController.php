<?php

namespace App\Modules\Identity\Infrastructure\Http\Controllers\Actions;

use App\Modules\Identity\Infrastructure\Http\Controllers\PermissionController;

class ListPermissionController
{
    public function __construct(private PermissionController $controller) {}

    public function __invoke()
    {
        return $this->controller->index();
    }
}
