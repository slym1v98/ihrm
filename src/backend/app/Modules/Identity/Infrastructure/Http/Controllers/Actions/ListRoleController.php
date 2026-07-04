<?php

namespace App\Modules\Identity\Infrastructure\Http\Controllers\Actions;

use App\Modules\Identity\Infrastructure\Http\Controllers\RoleController;
use Illuminate\Http\Request;

class ListRoleController
{
    public function __construct(private RoleController $controller) {}

    public function __invoke(Request $request)
    {
        return $this->controller->index($request);
    }
}
