<?php

namespace App\Modules\Asset\Infrastructure\Http\Requests;

use App\Http\Requests\BaseFormRequest;

class AssignAssetRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asset_id' => 'required|uuid|exists:assets,id',
            'employee_id' => 'required|uuid|exists:employees,id',
            'assigned_at' => 'sometimes|date_format:Y-m-d',
            'note' => 'sometimes|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'asset_id.required' => 'Vui lòng chọn tài sản',
            'asset_id.exists' => 'Tài sản không tồn tại',
            'employee_id.required' => 'Vui lòng chọn nhân viên',
            'employee_id.exists' => 'Nhân viên không tồn tại',
        ];
    }
}
