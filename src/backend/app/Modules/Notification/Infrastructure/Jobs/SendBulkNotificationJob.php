<?php

namespace App\Modules\Notification\Infrastructure\Jobs;

use App\Modules\Notification\Infrastructure\Persistence\Eloquent\NotificationOutboxModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBulkNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;

    public function __construct(
        private readonly array $notificationIds,
    ) {}

    public function handle(): void
    {
        $notifications = NotificationOutboxModel::whereIn('id', $this->notificationIds)
            ->where('status', 'pending')
            ->get();

        foreach ($notifications as $notification) {
            try {
                $notification->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to send notification', [
                    'id' => $notification->id,
                    'error' => $e->getMessage(),
                ]);
                $notification->update([
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
