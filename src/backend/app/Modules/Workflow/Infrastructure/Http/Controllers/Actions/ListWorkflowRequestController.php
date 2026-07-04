<?php

namespace App\Modules\Workflow\Infrastructure\Http\Controllers\Actions;

use App\Modules\Workflow\Application\QueryHandlers\ListWorkflowRequestsHandler;
use App\Modules\Workflow\Infrastructure\Http\Controllers\WorkflowRequestController;
use Illuminate\Http\Request;

class ListWorkflowRequestController
{
    public function __construct(private WorkflowRequestController $controller) {}

    public function __invoke(Request $request, ListWorkflowRequestsHandler $handler)
    {
        return $this->controller->index($request, $handler);
    }
}
