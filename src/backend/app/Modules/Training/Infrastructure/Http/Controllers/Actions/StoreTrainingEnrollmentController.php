<?php

namespace App\Modules\Training\Infrastructure\Http\Controllers\Actions;

use App\Modules\Training\Infrastructure\Http\Controllers\TrainingEnrollmentController;
use Illuminate\Http\Request;

class StoreTrainingEnrollmentController
{
    public function __construct(private TrainingEnrollmentController $controller) {}

    public function __invoke(Request $r, string $sessionId)
    {
        return $this->controller->store($r, $sessionId);
    }
}
