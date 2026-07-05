<?php

namespace App\Modules\Workflow\Infrastructure\Http\Controllers\Actions;

use App\Modules\Workflow\Application\QueryHandlers\ListWorkflowDelegationsHandler;
use App\Modules\Workflow\Infrastructure\Http\Controllers\WorkflowDelegationController;
use Illuminate\Http\Request;

class ListWorkflowDelegationController
{
    public function __construct(private WorkflowDelegationController $controller) {}

    public function __invoke(Request $request, ListWorkflowDelegationsHandler $handler)
    {
        return $this->controller->index($request, $handler);
    }
}
