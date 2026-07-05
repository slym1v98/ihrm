<?php

namespace App\Modules\Configuration\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HolidayCalendarModel extends Model
{
    use HasUuids;

    protected $table = 'holiday_calendars';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = ['active' => 'boolean', 'year' => 'integer'];

    public function holidays(): HasMany
    {
        return $this->hasMany(HolidayModel::class, 'calendar_id')->orderBy('date');
    }
}
