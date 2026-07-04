<?php

namespace App\Modules\Asset\Infrastructure\Http\Controllers\Actions;

use App\Modules\Asset\Infrastructure\Http\Controllers\AssetItemController;

class MarkAvailableAssetItemController
{
    public function __construct(private AssetItemController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->markAvailable($id);
    }
}
