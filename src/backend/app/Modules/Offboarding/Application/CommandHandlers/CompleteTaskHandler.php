<?php

namespace App\Modules\Offboarding\Application\CommandHandlers;

use App\Modules\Offboarding\Application\Commands\CompleteTaskCommand;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingTask\OffboardingTaskId;
use App\Modules\Offboarding\Domain\Exceptions\OffboardingTaskNotFoundException;
use App\Modules\Offboarding\Domain\Repositories\OffboardingTaskRepositoryInterface;
use App\Modules\Offboarding\Infrastructure\Services\TaskWorkflowService;

class CompleteTaskHandler
{
    public function __construct(
        private readonly OffboardingTaskRepositoryInterface $taskRepo,
        private readonly TaskWorkflowService $workflowService,
    ) {}

    public function handle(CompleteTaskCommand $command): void
    {
        $task = $this->taskRepo->findById(OffboardingTaskId::fromString($command->taskId));
        if (! $task) {
            throw new OffboardingTaskNotFoundException($command->taskId);
        }

        if ($task->isRequiresApproval() && $command->workflowTemplateId) {
            $requestId = $this->workflowService->startTaskApprovalWorkflow(
                $command->workflowTemplateId, $command->taskId,
            );
            $task->setApprovalWorkflowRequestId($requestId);
        }

        $task->complete($command->proofFileObjectId);
        $this->taskRepo->save($task);
        foreach ($task->popRecordedEvents() as $event) {
            event($event);
        }
    }
}
