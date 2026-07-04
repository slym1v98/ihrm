<?php

namespace App\Modules\Identity\Infrastructure\Http\Controllers\Actions;

use App\Modules\Identity\Infrastructure\Http\Controllers\AuthController;
use App\Modules\Identity\Infrastructure\Http\Requests\ChangePasswordRequest;

class ChangePasswordAuthController
{
    public function __construct(private AuthController $controller) {}

    public function __invoke(ChangePasswordRequest $request)
    {
        return $this->controller->changePassword($request);
    }
}
