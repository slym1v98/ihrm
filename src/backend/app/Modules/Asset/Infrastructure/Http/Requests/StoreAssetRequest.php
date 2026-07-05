<?php

namespace App\Modules\Asset\Infrastructure\Http\Requests;

use App\Http\Requests\BaseFormRequest;

class StoreAssetRequest extends BaseFormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:assets,code',
            'name' => 'required|string|max:200',
            'type' => 'required|string|max:50',
            'status' => 'sometimes|in:available,assigned,maintenance,retired',
            'description' => 'sometimes|string|max:1000',
        ];
    }
    public function messages(): array
    {
        return [
            'code.required' => 'Vui lòng nhập mã tài sản',
            'code.unique' => 'Mã tài sản đã tồn tại',
            'name.required' => 'Vui lòng nhập tên tài sản',
            'type.required' => 'Vui lòng chọn loại tài sản',
            'status.in' => 'Trạng thái không hợp lệ',
        ];
    }
}
