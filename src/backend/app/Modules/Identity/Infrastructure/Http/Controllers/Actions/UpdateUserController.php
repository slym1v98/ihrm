<?php

namespace App\Modules\Identity\Infrastructure\Http\Controllers\Actions;

use App\Modules\Identity\Infrastructure\Http\Controllers\UserController;
use App\Modules\Identity\Infrastructure\Http\Requests\UpdateUserRequest;

class UpdateUserController
{
    public function __construct(private UserController $controller) {}

    public function __invoke(string $id, UpdateUserRequest $request)
    {
        return $this->controller->update($id, $request);
    }
}
