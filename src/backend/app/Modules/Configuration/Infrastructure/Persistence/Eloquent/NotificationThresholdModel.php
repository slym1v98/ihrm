<?php

namespace App\Modules\Configuration\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class NotificationThresholdModel extends Model
{
    use HasUuids;

    protected $table = 'notification_thresholds';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
    protected $casts = ['active' => 'boolean', 'metadata' => 'array', 'days_before' => 'integer'];
}
