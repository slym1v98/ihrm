<?php

namespace App\Modules\Training\Infrastructure\Http\Controllers\Actions;

use App\Modules\Training\Infrastructure\Http\Controllers\TrainingSessionController;

class ListTrainingSessionController
{
    public function __construct(private TrainingSessionController $controller) {}

    public function __invoke(string $courseId)
    {
        return $this->controller->index($courseId);
    }
}
