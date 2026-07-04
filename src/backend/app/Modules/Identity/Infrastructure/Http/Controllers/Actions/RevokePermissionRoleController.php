<?php

namespace App\Modules\Identity\Infrastructure\Http\Controllers\Actions;

use App\Modules\Identity\Infrastructure\Http\Controllers\RoleController;

class RevokePermissionRoleController
{
    public function __construct(private RoleController $controller) {}

    public function __invoke(string $id, string $code)
    {
        return $this->controller->revokePermission($id, $code);
    }
}
