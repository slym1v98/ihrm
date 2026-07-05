<?php

namespace App\Modules\Notification\Infrastructure\Http\Requests;

use App\Http\Requests\BaseFormRequest;

class SendNotificationRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'required|uuid|exists:users,id',
            'type' => 'required|string|max:50',
            'title' => 'required|string|max:200',
            'body' => 'required|string',
            'channels' => 'sometimes|array',
            'channels.*' => 'in:email,sms,in_app',
        ];
    }

    public function messages(): array
    {
        return [
            'user_ids.required' => 'Vui lòng chọn người nhận',
            'user_ids.*.exists' => 'Người nhận không tồn tại',
            'type.required' => 'Vui lòng chọn loại thông báo',
            'title.required' => 'Vui lòng nhập tiêu đề',
            'body.required' => 'Vui lòng nhập nội dung',
        ];
    }
}
