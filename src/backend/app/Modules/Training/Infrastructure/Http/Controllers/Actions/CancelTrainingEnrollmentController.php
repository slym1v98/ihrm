<?php

namespace App\Modules\Training\Infrastructure\Http\Controllers\Actions;

use App\Modules\Training\Infrastructure\Http\Controllers\TrainingEnrollmentController;

class CancelTrainingEnrollmentController
{
    public function __construct(private TrainingEnrollmentController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->cancel($id);
    }
}
