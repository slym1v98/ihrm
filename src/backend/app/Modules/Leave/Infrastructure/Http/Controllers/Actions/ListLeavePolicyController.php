<?php

namespace App\Modules\Leave\Infrastructure\Http\Controllers\Actions;

use App\Modules\Leave\Infrastructure\Http\Controllers\LeavePolicyController;

class ListLeavePolicyController
{
    public function __construct(private LeavePolicyController $controller) {}

    public function __invoke()
    {
        return $this->controller->index();
    }
}
