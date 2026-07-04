<?php

namespace App\Modules\Performance\Infrastructure\Http\Controllers\Actions;

use App\Modules\Performance\Infrastructure\Http\Controllers\PerformanceCycleController;
use Illuminate\Http\Request;

class StorePerformanceCycleController
{
    public function __construct(private PerformanceCycleController $controller) {}

    public function __invoke(Request $r)
    {
        return $this->controller->store($r);
    }
}
