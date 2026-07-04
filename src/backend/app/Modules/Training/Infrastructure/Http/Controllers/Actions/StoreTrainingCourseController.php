<?php

namespace App\Modules\Training\Infrastructure\Http\Controllers\Actions;

use App\Modules\Training\Infrastructure\Http\Controllers\TrainingCourseController;
use Illuminate\Http\Request;

class StoreTrainingCourseController
{
    public function __construct(private TrainingCourseController $controller) {}

    public function __invoke(Request $request)
    {
        return $this->controller->store($request);
    }
}
