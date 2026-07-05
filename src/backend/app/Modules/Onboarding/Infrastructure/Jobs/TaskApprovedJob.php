<?php

namespace App\Modules\Onboarding\Infrastructure\Jobs;

use App\Modules\Onboarding\Domain\Repositories\OnboardingTaskRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class TaskApprovedJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        private readonly string $approvalWorkflowRequestId,
    ) {}

    public function handle(OnboardingTaskRepositoryInterface $taskRepo): void
    {
        $task = $taskRepo->findByApprovalWorkflowRequestId($this->approvalWorkflowRequestId);
        if (! $task) {
            return;
        }

        $task->markApproved();
        $taskRepo->save($task);

        foreach ($task->popRecordedEvents() as $event) {
            event($event);
        }
    }
}
