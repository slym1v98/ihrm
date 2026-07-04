<?php

namespace App\Modules\Performance\Infrastructure\Http\Controllers\Actions;

use App\Modules\Performance\Infrastructure\Http\Controllers\PerformanceCycleController;
use Illuminate\Http\Request;

class UpdatePerformanceCycleController
{
    public function __construct(private PerformanceCycleController $controller) {}

    public function __invoke(Request $r, string $id)
    {
        return $this->controller->update($r, $id);
    }
}
