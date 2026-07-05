<?php

namespace App\Modules\Organization\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'string', 'exists:branches,id'],
            'code' => ['required', 'string', 'regex:/^[A-Za-z][A-Za-z0-9-]{1,49}$/'],
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'string', 'exists:departments,id'],
        ];
    }
}
