<?php

namespace App\Modules\Workflow\Infrastructure\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WorkflowTemplateResource extends JsonResource
{
    public function toArray($request)
    {
        $template = $this->resource;

        return [
            'id' => $template->id()->value(),
            'code' => $template->code(),
            'name' => $template->name(),
            'description' => $template->description(),
            'active' => $template->isActive(),
            'steps' => array_map(fn ($s) => [
                'step_order' => $s->stepOrder(),
                'name' => $s->name(),
                'assignee_type' => $s->assigneeType()->value,
                'assignee_id' => $s->assigneeId(),
                'condition' => $s->condition(),
                'resolver_type' => $s->resolverType(),
                'resolver_config' => $s->resolverConfig(),
                'execution_type' => $s->executionType(),
                'escalation_sla_hours' => $s->escalationSlaHours(),
                'escalation_target_type' => $s->escalationTargetType(),
                'escalation_target_config' => $s->escalationTargetConfig(),
                'form_schema' => $s->formSchema(),
            ], $template->steps()),
        ];
    }
}
