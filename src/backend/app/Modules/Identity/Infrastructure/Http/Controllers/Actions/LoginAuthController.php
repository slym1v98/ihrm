<?php

namespace App\Modules\Identity\Infrastructure\Http\Controllers\Actions;

use App\Modules\Identity\Infrastructure\Http\Controllers\AuthController;
use App\Modules\Identity\Infrastructure\Http\Requests\LoginRequest;

class LoginAuthController
{
    public function __construct(private AuthController $controller) {}

    public function __invoke(LoginRequest $request)
    {
        return $this->controller->login($request);
    }
}
