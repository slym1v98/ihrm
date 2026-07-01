<?php

namespace App\Modules\Organization\Infrastructure\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'branch_id' => $this->branch_id,
            'parent_id' => $this->parent_id,
            'code' => $this->code,
            'name' => $this->name,
            'manager_employee_id' => $this->manager_employee_id,
            'status' => $this->status,
            'parent' => $this->whenLoaded('parent', fn () => new self($this->parent)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
