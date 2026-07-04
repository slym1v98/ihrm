<?php

namespace App\Modules\Workflow\Infrastructure\Http\Controllers\Actions;

use App\Modules\Workflow\Application\CommandHandlers\CancelWorkflowRequestHandler;
use App\Modules\Workflow\Infrastructure\Http\Controllers\WorkflowRequestController;
use App\Modules\Workflow\Infrastructure\Http\Requests\DecisionRequest;

class CancelWorkflowRequestController
{
    public function __construct(private WorkflowRequestController $controller) {}

    public function __invoke(string $id, DecisionRequest $request, CancelWorkflowRequestHandler $handler)
    {
        return $this->controller->cancel($id, $request, $handler);
    }
}
