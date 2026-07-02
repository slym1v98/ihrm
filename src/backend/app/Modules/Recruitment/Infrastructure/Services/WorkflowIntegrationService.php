<?php
namespace App\Modules\Recruitment\Infrastructure\Services;

use Ramsey\Uuid\Uuid;

class WorkflowIntegrationService
{
    public function startRequisitionApproval(string $requisitionId, string $submittedBy): string
    {
        return Uuid::uuid7()->toString();
    }
}
