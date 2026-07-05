<?php

namespace App\Modules\Onboarding\Application\CommandHandlers;

use App\Modules\Onboarding\Application\Commands\CompleteTaskCommand;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTaskId;
use App\Modules\Onboarding\Domain\Exceptions\OnboardingTaskNotFoundException;
use App\Modules\Onboarding\Domain\Repositories\OnboardingTaskRepositoryInterface;
use App\Modules\Onboarding\Infrastructure\Services\TaskWorkflowService;

class CompleteTaskHandler
{
    public function __construct(
        private readonly OnboardingTaskRepositoryInterface $taskRepo,
        private readonly TaskWorkflowService $workflowService,
    ) {}

    public function handle(CompleteTaskCommand $command): void
    {
        $task = $this->taskRepo->findById(OnboardingTaskId::fromString($command->taskId));
        if (! $task) {
            throw new OnboardingTaskNotFoundException($command->taskId);
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
