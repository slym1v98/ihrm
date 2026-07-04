<?php

namespace App\Modules\Identity\Infrastructure\Http\Controllers\Actions;

use App\Modules\Identity\Infrastructure\Http\Controllers\UserController;
use App\Modules\Identity\Infrastructure\Http\Requests\AssignRoleRequest;

class AssignRoleUserController
{
    public function __construct(private UserController $controller) {}

    public function __invoke(string $id, AssignRoleRequest $request)
    {
        return $this->controller->assignRole($id, $request);
    }
}
