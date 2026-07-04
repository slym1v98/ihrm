<?php

namespace App\Modules\Organization\Infrastructure\Http\Controllers\Actions;

use App\Modules\Organization\Infrastructure\Http\Controllers\BranchController;
use Illuminate\Http\Request;

class ActivateBranchController
{
    public function __construct(private BranchController $controller) {}

    public function __invoke(Request $request, string $id)
    {
        return $this->controller->activate($request, $id);
    }
}
