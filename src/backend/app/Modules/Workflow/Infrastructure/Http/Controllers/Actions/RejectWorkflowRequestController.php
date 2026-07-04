<?php

namespace App\Modules\Workflow\Infrastructure\Http\Controllers\Actions;

use App\Modules\Workflow\Application\CommandHandlers\RejectWorkflowStepHandler;
use App\Modules\Workflow\Infrastructure\Http\Controllers\WorkflowRequestController;
use App\Modules\Workflow\Infrastructure\Http\Requests\DecisionRequest;

class RejectWorkflowRequestController
{
    public function __construct(private WorkflowRequestController $controller) {}

    public function __invoke(string $id, DecisionRequest $request, RejectWorkflowStepHandler $handler)
    {
        return $this->controller->reject($id, $request, $handler);
    }
}
