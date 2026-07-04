<?php

namespace App\Modules\Workflow\Application\CommandHandlers;

use App\Modules\Workflow\Application\Commands\RejectWorkflowStepCommand;
use App\Modules\Workflow\Domain\Events\WorkflowRejected;
use App\Modules\Workflow\Domain\Aggregates\WorkflowRequest\WorkflowRequestId;
use App\Modules\Workflow\Domain\Exceptions\WorkflowRequestNotFoundException;
use App\Modules\Workflow\Domain\Repositories\WorkflowRequestRepositoryInterface;
use Illuminate\Support\Facades\Event;

class RejectWorkflowStepHandler
{
    public function __construct(private WorkflowRequestRepositoryInterface $requests) {}

    public function handle(RejectWorkflowStepCommand $command): void
    {
        $request = $this->requests->findById(new WorkflowRequestId($command->workflowRequestId));
        if (! $request) throw new WorkflowRequestNotFoundException();
        $request->rejectStep($command->actorId, $request->currentStep() ?? 0, $command->comment);
        $this->requests->save($request);
        Event::dispatch(new WorkflowRejected([
            'request_id' => $request->id()->value(),
            'subject_type' => $request->subjectType(),
            'subject_id' => $request->subjectId(),
            'actor_id' => $command->actorId,
            'comment' => $command->comment,
        ]));
    }
}
