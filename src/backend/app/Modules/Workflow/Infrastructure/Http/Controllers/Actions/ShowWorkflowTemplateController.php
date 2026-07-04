<?php

namespace App\Modules\Workflow\Infrastructure\Http\Controllers\Actions;

use App\Modules\Workflow\Application\QueryHandlers\GetWorkflowTemplateHandler;
use App\Modules\Workflow\Infrastructure\Http\Controllers\WorkflowTemplateController;

class ShowWorkflowTemplateController
{
    public function __construct(private WorkflowTemplateController $controller) {}

    public function __invoke(string $id, GetWorkflowTemplateHandler $handler)
    {
        return $this->controller->show($id, $handler);
    }
}
