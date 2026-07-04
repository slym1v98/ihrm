<?php

namespace App\Modules\Leave\Infrastructure\Http\Resources;

use App\Modules\Leave\Infrastructure\Persistence\Eloquent\LeaveRequestModel;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveRequestResource extends JsonResource
{
    public function toArray($request): array
    {
        if ($this->resource instanceof LeaveRequestModel) {
            return [
                'id' => $this->resource->id,
                'employee_id' => $this->resource->employee_id,
                'leave_type_id' => $this->resource->leave_type_id,
                'start_at' => $this->resource->start_at?->toDateString(),
                'end_at' => $this->resource->end_at?->toDateString(),
                'duration_unit' => $this->resource->duration_unit,
                'duration_minutes' => $this->resource->duration_minutes,
                'reason' => $this->resource->reason,
                'status' => $this->resource->status,
                'approved_by' => $this->resource->approved_by,
                'approved_at' => $this->resource->approved_at?->toIso8601String(),
                'rejected_reason' => $this->resource->rejected_reason,
                'created_at' => $this->resource->created_at?->toIso8601String(),
                'updated_at' => $this->resource->updated_at?->toIso8601String(),
            ];
        }

        return [
            'id' => $this->resource->id()->value(),
            'employee_id' => $this->resource->employeeId(),
            'leave_type_id' => $this->resource->leaveTypeId()->value(),
            'start_at' => $this->resource->period()->startAt()->toDateString(),
            'end_at' => $this->resource->period()->endAt()->toDateString(),
            'duration_unit' => $this->resource->durationUnit()->value,
            'duration_minutes' => $this->resource->period()->durationMinutes(),
            'reason' => $this->resource->reason(),
            'status' => $this->resource->status()->value,
            'approved_by' => $this->resource->approvedBy(),
            'approved_at' => $this->resource->approvedAt()?->toIso8601String(),
            'rejected_reason' => $this->resource->rejectedReason(),
            'created_at' => null,
            'updated_at' => null,
        ];
    }
}
