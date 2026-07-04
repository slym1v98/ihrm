<?php

namespace App\Modules\Asset\Infrastructure\Http\Controllers\Actions;

use App\Modules\Asset\Infrastructure\Http\Controllers\AssetAssignmentController;
use Illuminate\Http\Request;

class ReturnAssetAssetAssignmentController
{
    public function __construct(private AssetAssignmentController $controller) {}

    public function __invoke(Request $request, string $id)
    {
        return $this->controller->returnAsset($request, $id);
    }
}
