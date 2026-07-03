<?php

namespace App\Modules\Onboarding\Infrastructure\Services;

class TaskWorkflowService
{
    public function startTaskApprovalWorkflow(string $workflowTemplateId, string $taskId): string
    {
        throw new \RuntimeException('Workflow BC integration not yet wired');
    }
}
