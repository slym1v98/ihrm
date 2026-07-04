<?php

namespace App\Modules\Identity\Infrastructure\Http\Controllers\Actions;

use App\Modules\Identity\Infrastructure\Http\Controllers\UserController;
use App\Modules\Identity\Infrastructure\Http\Requests\CreateUserRequest;

class StoreUserController
{
    public function __construct(private UserController $controller) {}

    public function __invoke(CreateUserRequest $request)
    {
        return $this->controller->store($request);
    }
}
