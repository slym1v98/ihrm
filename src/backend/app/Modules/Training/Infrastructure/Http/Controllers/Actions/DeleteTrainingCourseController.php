<?php

namespace App\Modules\Training\Infrastructure\Http\Controllers\Actions;

use App\Modules\Training\Infrastructure\Http\Controllers\TrainingCourseController;

class DeleteTrainingCourseController
{
    public function __construct(private TrainingCourseController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->destroy($id);
    }
}
