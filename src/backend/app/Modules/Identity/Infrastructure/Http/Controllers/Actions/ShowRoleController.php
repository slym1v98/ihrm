<?php

namespace App\Modules\Identity\Infrastructure\Http\Controllers\Actions;

use App\Modules\Identity\Infrastructure\Http\Controllers\RoleController;

class ShowRoleController
{
    public function __construct(private RoleController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->show($id);
    }
}
