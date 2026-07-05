<?php

namespace App\Modules\Workflow\Infrastructure\Http\Controllers\Actions;

use App\Modules\Workflow\Application\CommandHandlers\RevokeWorkflowDelegationHandler;
use App\Modules\Workflow\Infrastructure\Http\Controllers\WorkflowDelegationController;

class DeleteWorkflowDelegationController
{
    public function __construct(private WorkflowDelegationController $controller) {}

    public function __invoke(string $id, RevokeWorkflowDelegationHandler $handler)
    {
        return $this->controller->destroy($id, $handler);
    }
}
