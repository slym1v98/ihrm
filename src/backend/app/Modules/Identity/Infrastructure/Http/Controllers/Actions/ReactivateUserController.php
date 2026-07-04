<?php

namespace App\Modules\Identity\Infrastructure\Http\Controllers\Actions;

use App\Modules\Identity\Infrastructure\Http\Controllers\UserController;

class ReactivateUserController
{
    public function __construct(private UserController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->reactivate($id);
    }
}
