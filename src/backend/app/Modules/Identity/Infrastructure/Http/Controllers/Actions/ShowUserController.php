<?php

namespace App\Modules\Identity\Infrastructure\Http\Controllers\Actions;

use App\Modules\Identity\Infrastructure\Http\Controllers\UserController;

class ShowUserController
{
    public function __construct(private UserController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->show($id);
    }
}
