<?php

namespace App\Modules\Training\Infrastructure\Http\Controllers\Actions;

use App\Modules\Training\Infrastructure\Http\Controllers\TrainingResultController;
use Illuminate\Http\Request;

class StoreTrainingResultController
{
    public function __construct(private TrainingResultController $controller) {}

    public function __invoke(Request $r, string $id)
    {
        return $this->controller->store($r, $id);
    }
}
