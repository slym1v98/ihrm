<?php

namespace App\Modules\Onboarding\Application\Commands;

class CompleteTaskCommand
{
    public function __construct(
        public readonly string $taskId,
        public readonly ?string $proofFileObjectId = null,
        public readonly ?string $workflowTemplateId = null,
    ) {}
}
