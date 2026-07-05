<?php

namespace App\Modules\Notification\Infrastructure\Seeders;

use App\Modules\Notification\Infrastructure\Persistence\Eloquent\NotificationMessageModel;
use App\Modules\Notification\Infrastructure\Persistence\Eloquent\NotificationOutboxModel;
use App\Modules\Identity\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Database\Seeder;

class DemoNotificationSeeder extends Seeder
{
    public function run(): void
    {
        $admin = UserModel::where('email', 'admin@ihrm.local')->first();
        if (!$admin) return;

        $msg = NotificationMessageModel::firstOrCreate(
            ['template_code' => 'ATTENDANCE_REMINDER', 'recipient_user_id' => $admin->id],
            [
                'channel' => 'in_app',
                'recipient_user_id' => $admin->id,
                'priority' => 'normal',
                'subject_rendered' => 'Nhắc nhở: Cập nhật chấm công',
                'body_rendered' => 'Vui lòng kiểm tra và cập nhật chấm công trước ngày 05.',
                'status' => 'sent',
            ],
        );

        NotificationOutboxModel::firstOrCreate(
            ['notification_message_id' => $msg->id],
            ['channel' => 'in_app', 'status' => 'pending', 'available_at' => now()->addDays(1)],
        );
    }
}
