<?php

namespace App\Modules\Offboarding\Infrastructure\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Modules\Offboarding\Domain\Repositories\OffboardingTaskRepositoryInterface;

class TaskApprovedJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        private readonly string $approvalWorkflowRequestId,
    ) {}

    public function handle(OffboardingTaskRepositoryInterface $taskRepo): void
    {
        $task = $taskRepo->findByApprovalWorkflowRequestId($this->approvalWorkflowRequestId);
        if (!$task) { return; }

        $task->markApproved();
        $taskRepo->save($task);

        foreach ($task->popRecordedEvents() as $event) { event($event); }
    }
}
