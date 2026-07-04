<?php

namespace App\Modules\Identity\Infrastructure\Http\Controllers\Actions;

use App\Modules\Identity\Infrastructure\Http\Controllers\RoleController;
use App\Modules\Identity\Infrastructure\Http\Requests\UpdateRoleRequest;

class UpdateRoleController
{
    public function __construct(private RoleController $controller) {}

    public function __invoke(string $id, UpdateRoleRequest $request)
    {
        return $this->controller->update($id, $request);
    }
}
