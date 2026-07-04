<?php

namespace App\Modules\Organization\Infrastructure\Http\Controllers\Actions;

use App\Modules\Organization\Infrastructure\Http\Controllers\BranchController;
use App\Modules\Organization\Infrastructure\Http\Requests\CreateBranchRequest;

class StoreBranchController
{
    public function __construct(private BranchController $controller) {}

    public function __invoke(CreateBranchRequest $request)
    {
        return $this->controller->store($request);
    }
}
