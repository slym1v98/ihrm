<?php

namespace App\Modules\Performance\Infrastructure\Http\Controllers\Actions;

use App\Modules\Performance\Infrastructure\Http\Controllers\PerformanceCycleController;

class ActivatePerformanceCycleController
{
    public function __construct(private PerformanceCycleController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->activate($id);
    }
}
