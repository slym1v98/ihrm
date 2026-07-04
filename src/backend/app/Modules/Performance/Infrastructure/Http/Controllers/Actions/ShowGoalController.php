<?php

namespace App\Modules\Performance\Infrastructure\Http\Controllers\Actions;

use App\Modules\Performance\Infrastructure\Http\Controllers\GoalController;

class ShowGoalController
{
    public function __construct(private GoalController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->show($id);
    }
}
