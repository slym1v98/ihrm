<?php

namespace App\Modules\Training\Infrastructure\Http\Controllers\Actions;

use App\Modules\Training\Infrastructure\Http\Controllers\TrainingResultController;

class ShowTrainingResultController
{
    public function __construct(private TrainingResultController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->show($id);
    }
}
