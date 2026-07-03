<?php

namespace App\Modules\Offboarding\Infrastructure\Services;

class TaskWorkflowService
{
    public function startTaskApprovalWorkflow(string $workflowTemplateId, string $taskId): string
    {
        throw new \RuntimeException('Workflow BC integration not yet wired');
    }
}
