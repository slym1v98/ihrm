<?php

namespace App\Modules\Configuration\Infrastructure\Http\Requests;

use App\Modules\Configuration\Infrastructure\Persistence\Eloquent\SystemSettingModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreSystemSettingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:150'],
            'value' => ['nullable', 'string'],
            'value_type' => ['string', 'max:30'],
            'group' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'editable' => ['boolean'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            $setting = SystemSettingModel::where('key', $this->input('key'))->first();
            if ($setting && ! $setting->editable) {
                $validator->errors()->add('key', 'Setting is not editable.');
            }
        }];
    }
}
