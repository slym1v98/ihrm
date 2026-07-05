<?php

namespace App\Modules\Organization\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'manager_employee_id' => ['nullable', 'string'],
        ];
    }
}
