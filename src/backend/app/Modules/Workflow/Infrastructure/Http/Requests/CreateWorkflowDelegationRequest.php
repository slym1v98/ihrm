<?php

namespace App\Modules\Workflow\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateWorkflowDelegationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'delegator_id' => ['required', 'string'],
            'delegate_id' => ['required', 'string', 'different:delegator_id'],
            'role_type' => ['nullable', 'string', 'max:100'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after_or_equal:start_at'],
        ];
    }
}
