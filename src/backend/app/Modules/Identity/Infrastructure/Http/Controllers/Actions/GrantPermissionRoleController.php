<?php

namespace App\Modules\Identity\Infrastructure\Http\Controllers\Actions;

use App\Modules\Identity\Infrastructure\Http\Controllers\RoleController;
use App\Modules\Identity\Infrastructure\Http\Requests\GrantRolePermissionRequest;

class GrantPermissionRoleController
{
    public function __construct(private RoleController $controller) {}

    public function __invoke(string $id, GrantRolePermissionRequest $request)
    {
        return $this->controller->grantPermission($id, $request);
    }
}
