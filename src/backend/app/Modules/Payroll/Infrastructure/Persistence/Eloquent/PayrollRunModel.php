<?php

namespace App\Modules\Payroll\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;


class PayrollRunModel extends Model
{
    use HasUuids;
    protected $table = 'payroll_runs';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['id','period_id','run_type','status','formula_version','triggered_by','started_at','completed_at','error_summary'];
    protected $casts = ['started_at'=>'datetime','completed_at'=>'datetime'];
}
