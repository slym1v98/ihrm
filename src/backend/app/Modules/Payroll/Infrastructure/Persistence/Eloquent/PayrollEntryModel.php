<?php

namespace App\Modules\Payroll\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PayrollEntryModel extends Model
{
    use HasUuids;
    protected $table = 'payroll_entries';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['id','run_id','period_id','employee_id','gross_amount','deduction_amount','net_amount','status','error_message','reviewed_by','reviewed_at','contract_snapshot','attendance_snapshot','leave_snapshot'];
    protected $casts = [
        'gross_amount' => 'decimal:2',
        'deduction_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'contract_snapshot' => 'array',
        'attendance_snapshot' => 'array',
        'leave_snapshot' => 'array',
    ];
    public function lines()
    {
        return $this->hasMany(\App\Modules\Payroll\Infrastructure\Persistence\Eloquent\PayrollEntryLineModel::class, 'entry_id');
    }
}
