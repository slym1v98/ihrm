<?php

namespace App\Modules\Organization\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'level' => ['nullable', 'integer', 'min:1', 'max:99'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
