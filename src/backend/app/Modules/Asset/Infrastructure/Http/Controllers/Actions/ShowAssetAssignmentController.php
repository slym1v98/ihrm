<?php

namespace App\Modules\Asset\Infrastructure\Http\Controllers\Actions;

use App\Modules\Asset\Infrastructure\Http\Controllers\AssetAssignmentController;

class ShowAssetAssignmentController
{
    public function __construct(private AssetAssignmentController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->show($id);
    }
}
