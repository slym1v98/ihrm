<?php

namespace App\Modules\Attendance\Infrastructure\Http\Requests;

use App\Http\Requests\BaseFormRequest;

class StoreAttendanceRawLogRequest extends BaseFormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'employee_id' => 'required|uuid|exists:employees,id',
            'device_id' => 'required|string|max:100',
            'timestamp' => 'required|date_format:Y-m-d H:i:s',
            'mode' => 'sometimes|in:in,out',
        ];
    }
    public function messages(): array
    {
        return [
            'employee_id.required' => 'Vui lòng chọn nhân viên',
            'employee_id.exists' => 'Nhân viên không tồn tại',
            'device_id.required' => 'Vui lòng nhập mã thiết bị',
            'timestamp.required' => 'Vui lòng nhập thời gian',
            'timestamp.date_format' => 'Thời gian không đúng định dạng (YYYY-MM-DD HH:MM:SS)',
            'mode.in' => 'Chế độ không hợp lệ (in/out)',
        ];
    }
}
