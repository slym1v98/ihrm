<?php

namespace App\Modules\Workflow\Infrastructure\Http\Controllers\Actions;

use App\Modules\Workflow\Application\CommandHandlers\CreateWorkflowDelegationHandler;
use App\Modules\Workflow\Infrastructure\Http\Controllers\WorkflowDelegationController;
use App\Modules\Workflow\Infrastructure\Http\Requests\CreateWorkflowDelegationRequest;

class StoreWorkflowDelegationController
{
    public function __construct(private WorkflowDelegationController $controller) {}
    public function __invoke(CreateWorkflowDelegationRequest $request, CreateWorkflowDelegationHandler $handler) { return $this->controller->store($request, $handler); }
}
