<?php

namespace App\Modules\Configuration\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHolidayRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['string', 'max:50'],
            'paid' => ['boolean'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
