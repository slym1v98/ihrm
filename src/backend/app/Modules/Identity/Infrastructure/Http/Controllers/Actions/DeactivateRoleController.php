<?php

namespace App\Modules\Identity\Infrastructure\Http\Controllers\Actions;

use App\Modules\Identity\Infrastructure\Http\Controllers\RoleController;

class DeactivateRoleController
{
    public function __construct(private RoleController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->deactivate($id);
    }
}
