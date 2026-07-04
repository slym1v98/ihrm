<?php

namespace App\Modules\Training\Infrastructure\Http\Controllers\Actions;

use App\Modules\Training\Infrastructure\Http\Controllers\TrainingSessionController;
use Illuminate\Http\Request;

class StoreTrainingSessionController
{
    public function __construct(private TrainingSessionController $controller) {}

    public function __invoke(Request $r, string $courseId)
    {
        return $this->controller->store($r, $courseId);
    }
}
