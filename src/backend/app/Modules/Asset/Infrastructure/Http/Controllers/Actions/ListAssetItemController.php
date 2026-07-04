<?php

namespace App\Modules\Asset\Infrastructure\Http\Controllers\Actions;

use App\Modules\Asset\Infrastructure\Http\Controllers\AssetItemController;
use Illuminate\Http\Request;

class ListAssetItemController
{
    public function __construct(private AssetItemController $controller) {}

    public function __invoke(Request $request)
    {
        return $this->controller->index($request);
    }
}
