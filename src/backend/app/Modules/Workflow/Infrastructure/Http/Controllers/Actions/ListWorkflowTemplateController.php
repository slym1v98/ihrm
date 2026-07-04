<?php

namespace App\Modules\Workflow\Infrastructure\Http\Controllers\Actions;

use App\Modules\Workflow\Application\QueryHandlers\ListWorkflowTemplatesHandler;
use App\Modules\Workflow\Infrastructure\Http\Controllers\WorkflowTemplateController;

class ListWorkflowTemplateController
{
    public function __construct(private WorkflowTemplateController $controller) {}

    public function __invoke(ListWorkflowTemplatesHandler $handler)
    {
        return $this->controller->index($handler);
    }
}
