<?php

namespace App\Modules\Workflow\Infrastructure\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WorkflowRequestResource extends JsonResource
{
    public function toArray($request)
    {
        $r = $this->resource;
        return [
            'id' => $r->id()->value(),
            'workflow_template_id' => $r->workflowTemplateId()->value(),
            'subject_type' => $r->subjectType(),
            'subject_id' => $r->subjectId(),
            'submitted_by' => $r->submittedBy(),
            'status' => $r->status()->value,
            'current_step' => $r->currentStep(),
            'context' => $r->context(),
            'parallel_approved_count' => $r->parallelApprovedCount(),
            'parallel_required_count' => $r->parallelRequiredCount(),
            'sla_deadline_at' => $r->slaDeadlineAt()?->toIso8601String(),
            'escalated' => $r->escalated(),
            'actions' => array_map(fn ($a) => [
                'id' => $a->id()->value(),
                'step_order' => $a->stepOrder(),
                'action' => $a->action()->value,
                'actor_id' => $a->actorId(),
                'comment' => $a->comment(),
                'resolved_approvers' => $a->resolvedApprovers(),
                'delegation_map' => $a->delegationMap(),
                'created_at' => $a->createdAt()->toIso8601String(),
            ], $r->actions()),
        ];
    }
}
