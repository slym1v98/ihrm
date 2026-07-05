<?php

namespace App\Modules\Notification\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class MessageTemplateModel extends Model
{
    use HasUuids;

    protected $table = 'notification_templates';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'code',
        'name',
        'channel',
        'subject',
        'body',
        'variables',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];
}
