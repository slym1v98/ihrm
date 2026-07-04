<?php

namespace App\Modules\Leave\Application\Workflow\Listeners;

use App\Modules\Leave\Application\Commands\LeaveRequest\RejectLeaveRequestCommand;
use App\Modules\Leave\Application\CommandHandlers\LeaveRequest\RejectLeaveRequestHandler;

class SyncLeaveRequestOnWorkflowRejected
{
    public function __construct(private RejectLeaveRequestHandler $handler) {}

    public function handle(object $event): void
    {
        $payload = $event->payload ?? [];
        if (($payload['subject_type'] ?? null) !== 'leave_request') {
            return;
        }

        $this->handler->handle(new RejectLeaveRequestCommand(
            $payload['subject_id'],
            $payload['actor_id'] ?? 'system',
            $payload['comment'] ?? 'Workflow rejected',
        ));
    }
}
