<?php

namespace App\Modules\Workflow\Application\CommandHandlers;

use App\Modules\Workflow\Application\Commands\ApproveWorkflowStepCommand;
use App\Modules\Workflow\Application\Services\WorkflowEngine;
use App\Modules\Workflow\Domain\Aggregates\WorkflowRequest\WorkflowRequestId;
use App\Modules\Workflow\Domain\Events\WorkflowApproved;
use App\Modules\Workflow\Domain\Exceptions\WorkflowRequestNotFoundException;
use App\Modules\Workflow\Domain\Exceptions\WorkflowTemplateNotFoundException;
use App\Modules\Workflow\Domain\Repositories\WorkflowRequestRepositoryInterface;
use App\Modules\Workflow\Domain\Repositories\WorkflowTemplateRepositoryInterface;
use Illuminate\Support\Facades\Event;

class ApproveWorkflowStepHandler
{
    public function __construct(private WorkflowRequestRepositoryInterface $requests, private WorkflowTemplateRepositoryInterface $templates, private WorkflowEngine $engine) {}

    public function handle(ApproveWorkflowStepCommand $command): void
    {
        $request = $this->requests->findById(new WorkflowRequestId($command->workflowRequestId));
        if (! $request) {
            throw new WorkflowRequestNotFoundException;
        }
        $template = $this->templates->findById($request->workflowTemplateId());
        if (! $template) {
            throw new WorkflowTemplateNotFoundException;
        }
        $currentStep = $request->currentStep() ?? 0;
        $isFinal = $template->isFinalStep($currentStep);
        $request->approveStep($command->actorId, $currentStep, $isFinal, $command->comment);
        if (! $isFinal) {
            $this->engine->advanceAfterApproval($request, $template);
        }
        $this->requests->save($request);
        if ($request->status()->value === 'approved') {
            Event::dispatch(new WorkflowApproved([
                'request_id' => $request->id()->value(),
                'subject_type' => $request->subjectType(),
                'subject_id' => $request->subjectId(),
                'actor_id' => $command->actorId,
                'submitted_by' => $request->submittedBy(),
            ]));
        }
    }
}
