<?php

namespace App\Modules\Attendance\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AttendancePeriodModel extends Model
{
    use HasUuids;

    protected $table = 'attendance_periods';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'period_code',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];
}
