<?php

namespace App\Modules\Offboarding\Infrastructure\Jobs;

use App\Modules\Offboarding\Domain\Repositories\OffboardingTaskRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class TaskApprovedJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        private readonly string $approvalWorkflowRequestId,
    ) {}

    public function handle(OffboardingTaskRepositoryInterface $taskRepo): void
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
