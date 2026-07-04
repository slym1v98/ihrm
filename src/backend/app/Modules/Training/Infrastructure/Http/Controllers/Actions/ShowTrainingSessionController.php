<?php

namespace App\Modules\Training\Infrastructure\Http\Controllers\Actions;

use App\Modules\Training\Infrastructure\Http\Controllers\TrainingSessionController;

class ShowTrainingSessionController
{
    public function __construct(private TrainingSessionController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->show($id);
    }
}
