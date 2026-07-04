<?php

namespace App\Modules\Performance\Infrastructure\Http\Controllers\Actions;

use App\Modules\Performance\Infrastructure\Http\Controllers\GoalController;
use Illuminate\Http\Request;

class StoreGoalController
{
    public function __construct(private GoalController $controller) {}

    public function __invoke(Request $r)
    {
        return $this->controller->store($r);
    }
}
