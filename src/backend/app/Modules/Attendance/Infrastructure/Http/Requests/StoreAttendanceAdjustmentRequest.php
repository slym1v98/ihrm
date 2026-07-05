<?php

namespace App\Modules\Attendance\Infrastructure\Http\Requests;

use App\Http\Requests\BaseFormRequest;

class StoreAttendanceAdjustmentRequest extends BaseFormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|uuid|exists:employees,id',
            'date' => 'required|date_format:Y-m-d',
            'type' => 'required|string|in:clock_in,clock_out,late,early_leave,overtime',
            'reason' => 'required|string|max:500',
            'original_time' => 'sometimes|date_format:H:i:s',
            'adjusted_time' => 'required|date_format:H:i:s',
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Vui lòng chọn nhân viên',
            'employee_id.exists' => 'Nhân viên không tồn tại',
            'date.required' => 'Vui lòng chọn ngày',
            'date.date_format' => 'Ngày không đúng định dạng (YYYY-MM-DD)',
            'type.required' => 'Vui lòng chọn loại điều chỉnh',
            'type.in' => 'Loại điều chỉnh không hợp lệ',
            'reason.required' => 'Vui lòng nhập lý do',
            'reason.max' => 'Lý do không được vượt quá 500 ký tự',
            'adjusted_time.required' => 'Vui lòng nhập giờ điều chỉnh',
            'adjusted_time.date_format' => 'Giờ không đúng định dạng (HH:MM:SS)',
        ];
    }
}
