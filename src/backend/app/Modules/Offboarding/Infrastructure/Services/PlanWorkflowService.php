<?php

namespace App\Modules\Offboarding\Infrastructure\Services;

class PlanWorkflowService
{
    public function startWorkflow(string $workflowTemplateId, string $subjectType, string $subjectId): string
    {
        throw new \RuntimeException('Workflow BC integration not yet wired');
    }
}
