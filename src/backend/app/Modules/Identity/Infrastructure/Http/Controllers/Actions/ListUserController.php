<?php

namespace App\Modules\Identity\Infrastructure\Http\Controllers\Actions;

use App\Modules\Identity\Infrastructure\Http\Controllers\UserController;
use Illuminate\Http\Request;

class ListUserController
{
    public function __construct(private UserController $controller) {}

    public function __invoke(Request $request)
    {
        return $this->controller->index($request);
    }
}
