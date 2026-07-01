<?php

namespace App\Modules\Employee\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'contract_number' => $this->contract_number,
            'contract_type' => $this->contract_type,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'sign_date' => $this->sign_date?->format('Y-m-d'),
            'status' => $this->status,
            'base_salary' => $this->base_salary,
            'position_id' => $this->position_id,
            'predecessor_contract_id' => $this->predecessor_contract_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
