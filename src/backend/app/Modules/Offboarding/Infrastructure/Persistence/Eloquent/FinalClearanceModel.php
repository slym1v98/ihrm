<?php

namespace App\Modules\Offboarding\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class FinalClearanceModel extends Model
{
    use HasUuids;

    protected $table = 'final_clearances';

    protected $fillable = ['id', 'offboarding_plan_id', 'employee_id', 'cleared_at', 'cleared_by', 'asset_obligations_met', 'payroll_notes'];

    protected function casts(): array
    {
        return ['cleared_at' => 'datetime', 'asset_obligations_met' => 'boolean'];
    }
}
