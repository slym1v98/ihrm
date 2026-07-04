<?php

namespace App\Modules\Identity\Infrastructure\Http\Controllers\Actions;

use App\Modules\Identity\Infrastructure\Http\Controllers\AuthController;
use Illuminate\Http\Request;

class MeAuthController
{
    public function __construct(private AuthController $controller) {}

    public function __invoke(Request $request)
    {
        return $this->controller->me($request);
    }
}
