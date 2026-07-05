<?php

namespace App\Modules\Notification\Infrastructure\Http\Requests;

use App\Http\Requests\BaseFormRequest;

class StoreMessageTemplateRequest extends BaseFormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:message_templates,code',
            'name' => 'required|string|max:200',
            'type' => 'required|string|max:50',
            'channels' => 'required|array',
            'channels.*' => 'in:email,sms,in_app',
            'subject' => 'sometimes|string|max:200',
            'body' => 'required|string',
        ];
    }
    public function messages(): array
    {
        return [
            'code.required' => 'Vui lòng nhập mã mẫu tin nhắn',
            'code.unique' => 'Mã mẫu tin nhắn đã tồn tại',
            'name.required' => 'Vui lòng nhập tên mẫu tin nhắn',
            'type.required' => 'Vui lòng chọn loại',
            'channels.required' => 'Vui lòng chọn kênh gửi',
            'channels.*.in' => 'Kênh gửi không hợp lệ (email/sms/in_app)',
            'body.required' => 'Vui lòng nhập nội dung',
        ];
    }
}
