<?php

namespace App\Modules\Configuration\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHolidayCalendarRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'year' => ['required', 'integer', 'min:1900'],
            'active' => ['boolean'],
        ];
    }
}
