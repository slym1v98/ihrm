<?php

namespace App\Modules\Leave\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class LeaveBalanceModel extends Model
{
    use HasUuids;
    protected $table = 'leave_balances';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'employee_id',
        'leave_type_id',
        'year',
        'opening',
        'accrued',
        'used',
        'carried_over',
        'expired',
    ];

    protected $casts = [
        'year' => 'integer',
        'opening' => 'integer',
        'accrued' => 'integer',
        'used' => 'integer',
        'carried_over' => 'integer',
        'expired' => 'integer',
    ];
}
