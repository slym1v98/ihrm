<?php

namespace App\Modules\Organization\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePositionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'regex:/^[A-Za-z][A-Za-z0-9-]{1,49}$/'],
            'name' => ['required', 'string', 'max:255'],
            'level' => ['nullable', 'integer', 'min:1', 'max:99'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
