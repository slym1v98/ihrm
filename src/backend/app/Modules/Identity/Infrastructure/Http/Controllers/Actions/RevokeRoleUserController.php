<?php

namespace App\Modules\Identity\Infrastructure\Http\Controllers\Actions;

use App\Modules\Identity\Infrastructure\Http\Controllers\UserController;

class RevokeRoleUserController
{
    public function __construct(private UserController $controller) {}

    public function __invoke(string $id, string $roleId)
    {
        return $this->controller->revokeRole($id, $roleId);
    }
}
