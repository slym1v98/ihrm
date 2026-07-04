<?php

namespace App\Modules\Leave\Application\Workflow\Listeners;

use App\Modules\Leave\Application\Commands\LeaveRequest\ApproveLeaveRequestCommand;
use App\Modules\Leave\Application\CommandHandlers\LeaveRequest\ApproveLeaveRequestHandler;

class SyncLeaveRequestOnWorkflowApproved
{
    public function __construct(private ApproveLeaveRequestHandler $handler) {}

    public function handle(object $event): void
    {
        $payload = $event->payload ?? [];
        if (($payload['subject_type'] ?? null) !== 'leave_request') {
            return;
        }

        $this->handler->handle(new ApproveLeaveRequestCommand(
            $payload['subject_id'],
            $payload['actor_id'] ?? $payload['submitted_by'] ?? 'system',
        ));
    }
}
