<?php

namespace App\Modules\Leave\Infrastructure\Http\Controllers\Actions;

use App\Modules\Leave\Application\QueryHandlers\ListLeaveRequestsHandler;
use App\Modules\Leave\Infrastructure\Http\Controllers\LeaveRequestController;
use Illuminate\Http\Request;

class ListLeaveRequestController
{
    public function __construct(private LeaveRequestController $controller) {}

    public function __invoke(Request $req, ListLeaveRequestsHandler $handler)
    {
        return $this->controller->index($req, $handler);
    }
}
