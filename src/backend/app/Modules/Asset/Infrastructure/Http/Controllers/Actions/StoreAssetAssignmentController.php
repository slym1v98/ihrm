<?php

namespace App\Modules\Asset\Infrastructure\Http\Controllers\Actions;

use App\Modules\Asset\Infrastructure\Http\Controllers\AssetAssignmentController;
use Illuminate\Http\Request;

class StoreAssetAssignmentController
{
    public function __construct(private AssetAssignmentController $controller) {}

    public function __invoke(Request $request)
    {
        return $this->controller->store($request);
    }
}
