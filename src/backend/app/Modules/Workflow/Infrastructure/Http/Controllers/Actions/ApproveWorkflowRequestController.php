<?php

namespace App\Modules\Workflow\Infrastructure\Http\Controllers\Actions;

use App\Modules\Workflow\Application\CommandHandlers\ApproveWorkflowStepHandler;
use App\Modules\Workflow\Infrastructure\Http\Controllers\WorkflowRequestController;
use App\Modules\Workflow\Infrastructure\Http\Requests\DecisionRequest;

class ApproveWorkflowRequestController
{
    public function __construct(private WorkflowRequestController $controller) {}

    public function __invoke(string $id, DecisionRequest $request, ApproveWorkflowStepHandler $handler)
    {
        return $this->controller->approve($id, $request, $handler);
    }
}
