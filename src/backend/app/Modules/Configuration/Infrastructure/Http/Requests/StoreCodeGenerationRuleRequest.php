<?php

namespace App\Modules\Configuration\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCodeGenerationRuleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'entity_type' => ['required', 'string', 'max:100'],
            'prefix' => ['required', 'string', 'max:20'],
            'pattern' => ['required', 'string', 'max:255'],
            'next_number' => ['integer', 'min:1'],
            'sequence_padding' => ['integer', 'min:1', 'max:12'],
            'active' => ['boolean'],
        ];
    }
}
