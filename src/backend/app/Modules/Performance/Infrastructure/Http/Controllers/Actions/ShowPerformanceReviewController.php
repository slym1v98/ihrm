<?php

namespace App\Modules\Performance\Infrastructure\Http\Controllers\Actions;

use App\Modules\Performance\Infrastructure\Http\Controllers\PerformanceReviewController;

class ShowPerformanceReviewController
{
    public function __construct(private PerformanceReviewController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->show($id);
    }
}
