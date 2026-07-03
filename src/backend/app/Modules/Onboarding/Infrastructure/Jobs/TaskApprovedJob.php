<?php

namespace App\Modules\Onboarding\Infrastructure\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Modules\Onboarding\Domain\Repositories\OnboardingTaskRepositoryInterface;

class TaskApprovedJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        private readonly string $approvalWorkflowRequestId,
    ) {}

    public function handle(OnboardingTaskRepositoryInterface $taskRepo): void
    {
        $task = $taskRepo->findByApprovalWorkflowRequestId($this->approvalWorkflowRequestId);
        if (!$task) { return; }

        $task->markApproved();
        $taskRepo->save($task);

        foreach ($task->popRecordedEvents() as $event) { event($event); }
    }
}
