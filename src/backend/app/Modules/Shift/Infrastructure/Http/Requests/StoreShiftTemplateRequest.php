<?php

namespace App\Modules\Shift\Infrastructure\Http\Requests;

use App\Http\Requests\BaseFormRequest;

class StoreShiftTemplateRequest extends BaseFormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'code' => 'required|string|max:20|unique:shift_templates,code',
            'name' => 'required|string|max:200',
            'shift_type' => 'required|in:fixed,flexible,rotating',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'grace_minutes' => 'sometimes|integer|min:0|max:120',
            'description' => 'sometimes|string|max:500',
        ];
    }
    public function messages(): array
    {
        return [
            'code.required' => 'Vui lòng nhập mã ca',
            'code.unique' => 'Mã ca đã tồn tại',
            'name.required' => 'Vui lòng nhập tên ca',
            'shift_type.required' => 'Vui lòng chọn loại ca',
            'shift_type.in' => 'Loại ca không hợp lệ (fixed/flexible/rotating)',
            'start_time.required' => 'Vui lòng nhập giờ bắt đầu',
            'end_time.required' => 'Vui lòng nhập giờ kết thúc',
            'end_time.after' => 'Giờ kết thúc phải sau giờ bắt đầu',
        ];
    }
}
