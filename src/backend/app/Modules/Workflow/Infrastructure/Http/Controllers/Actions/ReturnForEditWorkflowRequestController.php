<?php

namespace App\Modules\Workflow\Infrastructure\Http\Controllers\Actions;

use App\Modules\Workflow\Application\CommandHandlers\ReturnWorkflowForEditHandler;
use App\Modules\Workflow\Infrastructure\Http\Controllers\WorkflowRequestController;
use App\Modules\Workflow\Infrastructure\Http\Requests\DecisionRequest;

class ReturnForEditWorkflowRequestController
{
    public function __construct(private WorkflowRequestController $controller) {}

    public function __invoke(string $id, DecisionRequest $request, ReturnWorkflowForEditHandler $handler)
    {
        return $this->controller->returnForEdit($id, $request, $handler);
    }
}
