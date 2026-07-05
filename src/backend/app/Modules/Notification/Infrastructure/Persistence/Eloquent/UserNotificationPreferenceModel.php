<?php

namespace App\Modules\Notification\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class UserNotificationPreferenceModel extends Model
{
    use HasUuids;
    protected $table = 'user_notification_preferences';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'channel',
        'template_code',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];
}
