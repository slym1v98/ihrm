<?php

namespace App\Modules\Configuration\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HolidayModel extends Model
{
    use HasUuids;

    protected $table = 'holidays';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
    protected $casts = ['date' => 'date', 'paid' => 'boolean', 'metadata' => 'array'];

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(HolidayCalendarModel::class, 'calendar_id');
    }
}
