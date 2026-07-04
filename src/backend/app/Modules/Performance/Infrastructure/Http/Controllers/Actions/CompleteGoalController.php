<?php

namespace App\Modules\Performance\Infrastructure\Http\Controllers\Actions;

use App\Modules\Performance\Infrastructure\Http\Controllers\GoalController;
use Illuminate\Http\Request;

class CompleteGoalController
{
    public function __construct(private GoalController $controller) {}

    public function __invoke(Request $r, string $id)
    {
        return $this->controller->complete($r, $id);
    }
}
