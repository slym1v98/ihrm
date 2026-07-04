<?php

namespace App\Modules\Organization\Infrastructure\Http\Controllers\Actions;

use App\Modules\Organization\Infrastructure\Http\Controllers\BranchController;
use Illuminate\Http\Request;

class ListBranchController
{
    public function __construct(private BranchController $controller) {}

    public function __invoke(Request $request)
    {
        return $this->controller->index($request);
    }
}
