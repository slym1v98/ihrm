<?php

namespace App\Modules\Workflow\Infrastructure\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WorkflowDelegationResource extends JsonResource
{
    public function toArray($request): array
    {
        $d = $this->resource;

        return [
            'id' => method_exists($d, 'id') ? $d->id()->value() : $d->id,
            'delegator_id' => method_exists($d, 'delegatorId') ? $d->delegatorId() : $d->delegator_id,
            'delegate_id' => method_exists($d, 'delegateId') ? $d->delegateId() : $d->delegate_id,
            'role_type' => method_exists($d, 'roleType') ? $d->roleType() : $d->role_type,
            'start_at' => (method_exists($d, 'startAt') ? $d->startAt() : $d->start_at)?->toIso8601String(),
            'end_at' => (method_exists($d, 'endAt') ? $d->endAt() : $d->end_at)?->toIso8601String(),
            'active' => method_exists($d, 'active') ? $d->active() : (bool) $d->active,
            'created_by' => method_exists($d, 'createdBy') ? $d->createdBy() : $d->created_by,
        ];
    }
}
