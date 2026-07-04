<?php

namespace App\Modules\Workflow\Infrastructure\Http\Controllers\Actions;

use App\Modules\Workflow\Application\QueryHandlers\GetWorkflowRequestHandler;
use App\Modules\Workflow\Infrastructure\Http\Controllers\WorkflowRequestController;

class ShowWorkflowRequestController
{
    public function __construct(private WorkflowRequestController $controller) {}

    public function __invoke(string $id, GetWorkflowRequestHandler $handler)
    {
        return $this->controller->show($id, $handler);
    }
}
