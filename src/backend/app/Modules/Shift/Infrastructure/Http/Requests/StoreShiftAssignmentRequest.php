<?php

namespace App\Modules\Shift\Infrastructure\Http\Requests;

use App\Http\Requests\BaseFormRequest;

class StoreShiftAssignmentRequest extends BaseFormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'employee_id' => 'required|uuid|exists:employees,id',
            'shift_template_id' => 'required|uuid|exists:shift_templates,id',
            'effective_date' => 'required|date_format:Y-m-d',
            'end_date' => 'sometimes|date_format:Y-m-d|after_or_equal:effective_date',
        ];
    }
    public function messages(): array
    {
        return [
            'employee_id.required' => 'Vui lòng chọn nhân viên',
            'employee_id.exists' => 'Nhân viên không tồn tại',
            'shift_template_id.required' => 'Vui lòng chọn ca làm việc',
            'shift_template_id.exists' => 'Ca làm việc không tồn tại',
            'effective_date.required' => 'Vui lòng chọn ngày hiệu lực',
            'end_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày hiệu lực',
        ];
    }
}
