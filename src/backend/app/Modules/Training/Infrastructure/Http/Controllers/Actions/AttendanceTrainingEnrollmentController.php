<?php

namespace App\Modules\Training\Infrastructure\Http\Controllers\Actions;

use App\Modules\Training\Infrastructure\Http\Controllers\TrainingEnrollmentController;
use Illuminate\Http\Request;

class AttendanceTrainingEnrollmentController
{
    public function __construct(private TrainingEnrollmentController $controller) {}

    public function __invoke(Request $r, string $id)
    {
        return $this->controller->attendance($r, $id);
    }
}
