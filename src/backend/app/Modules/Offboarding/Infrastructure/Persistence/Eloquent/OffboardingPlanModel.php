<?php

namespace App\Modules\Offboarding\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class OffboardingPlanModel extends Model
{
    use HasUuids;

    protected $table = 'offboarding_plans';

    protected $fillable = ['id', 'offboarding_request_id', 'status', 'completed_at'];

    protected function casts(): array
    {
        return ['completed_at' => 'datetime'];
    }

    public function tasks()
    {
        return $this->hasMany(OffboardingTaskModel::class, 'offboarding_plan_id');
    }
}
