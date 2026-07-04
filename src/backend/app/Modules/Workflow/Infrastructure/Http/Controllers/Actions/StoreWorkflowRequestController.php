<?php

namespace App\Modules\Workflow\Infrastructure\Http\Controllers\Actions;

use App\Modules\Workflow\Application\CommandHandlers\SubmitWorkflowRequestHandler;
use App\Modules\Workflow\Infrastructure\Http\Controllers\WorkflowRequestController;
use App\Modules\Workflow\Infrastructure\Http\Requests\StartWorkflowRequestRequest;

class StoreWorkflowRequestController
{
    public function __construct(private WorkflowRequestController $controller) {}

    public function __invoke(StartWorkflowRequestRequest $request, SubmitWorkflowRequestHandler $handler)
    {
        return $this->controller->store($request, $handler);
    }
}
