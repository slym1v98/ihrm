<?php

namespace App\Modules\Configuration\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNotificationThresholdRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:100'],
            'target_type' => ['required', 'string', 'max:100'],
            'days_before' => ['required', 'integer', 'min:0'],
            'channel' => ['string', 'max:50'],
            'active' => ['boolean'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
