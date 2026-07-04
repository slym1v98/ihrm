<?php

namespace App\Modules\Asset\Infrastructure\Http\Controllers\Actions;

use App\Modules\Asset\Infrastructure\Http\Controllers\AssetItemController;
use Illuminate\Http\Request;

class UpdateAssetItemController
{
    public function __construct(private AssetItemController $controller) {}

    public function __invoke(Request $request, string $id)
    {
        return $this->controller->update($request, $id);
    }
}
