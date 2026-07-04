<?php

namespace App\Modules\Identity\Infrastructure\Http\Controllers\Actions;

use App\Modules\Identity\Infrastructure\Http\Controllers\RoleController;
use App\Modules\Identity\Infrastructure\Http\Requests\CreateRoleRequest;

class StoreRoleController
{
    public function __construct(private RoleController $controller) {}

    public function __invoke(CreateRoleRequest $request)
    {
        return $this->controller->store($request);
    }
}
