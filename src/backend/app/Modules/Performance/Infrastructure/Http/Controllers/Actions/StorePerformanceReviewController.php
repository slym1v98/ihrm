<?php

namespace App\Modules\Performance\Infrastructure\Http\Controllers\Actions;

use App\Modules\Performance\Infrastructure\Http\Controllers\PerformanceReviewController;
use Illuminate\Http\Request;

class StorePerformanceReviewController
{
    public function __construct(private PerformanceReviewController $controller) {}

    public function __invoke(Request $r)
    {
        return $this->controller->store($r);
    }
}
