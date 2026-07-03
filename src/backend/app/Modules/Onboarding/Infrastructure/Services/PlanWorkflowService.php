<?php

namespace App\Modules\Onboarding\Infrastructure\Services;

class PlanWorkflowService
{
    public function startWorkflow(string $workflowTemplateId, string $subjectType, string $subjectId): string
    {
        throw new \RuntimeException('Workflow BC integration not yet wired');
    }
}
