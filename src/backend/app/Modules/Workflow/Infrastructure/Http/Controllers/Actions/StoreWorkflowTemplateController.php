<?php

namespace App\Modules\Workflow\Infrastructure\Http\Controllers\Actions;

use App\Modules\Workflow\Application\CommandHandlers\CreateWorkflowTemplateHandler;
use App\Modules\Workflow\Infrastructure\Http\Controllers\WorkflowTemplateController;
use App\Modules\Workflow\Infrastructure\Http\Requests\CreateWorkflowTemplateRequest;

class StoreWorkflowTemplateController
{
    public function __construct(private WorkflowTemplateController $controller) {}

    public function __invoke(CreateWorkflowTemplateRequest $request, CreateWorkflowTemplateHandler $handler)
    {
        return $this->controller->store($request, $handler);
    }
}
