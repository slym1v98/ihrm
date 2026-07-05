<?php

namespace App\Modules\Notification\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class NotificationMessageModel extends Model
{
    use HasUuids;
    protected $table = 'notification_messages';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'template_code',
        'channel',
        'recipient_user_id',
        'recipient_address',
        'subject_rendered',
        'body_rendered',
        'payload',
        'status',
        'priority',
        'error',
        'read_at',
        'sent_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'read_at' => 'datetime',
        'sent_at' => 'datetime',
    ];
}
