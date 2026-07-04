<?php

namespace App\Modules\Organization\Infrastructure\Http\Controllers\Actions;

use App\Modules\Organization\Infrastructure\Http\Controllers\BranchController;
use App\Modules\Organization\Infrastructure\Http\Requests\UpdateBranchRequest;

class UpdateBranchController
{
    public function __construct(private BranchController $controller) {}

    public function __invoke(UpdateBranchRequest $request, string $id)
    {
        return $this->controller->update($request, $id);
    }
}
