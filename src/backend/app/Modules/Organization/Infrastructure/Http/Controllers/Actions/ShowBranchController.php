<?php

namespace App\Modules\Organization\Infrastructure\Http\Controllers\Actions;

use App\Modules\Organization\Infrastructure\Http\Controllers\BranchController;

class ShowBranchController
{
    public function __construct(private BranchController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->show($id);
    }
}
