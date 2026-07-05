<?php

namespace App\Modules\Onboarding\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class OnboardingPlanModel extends Model
{
    use HasUuids;

    protected $table = 'onboarding_plans';

    protected $fillable = [
        'id', 'employee_id', 'candidate_id', 'template_id',
        'start_date', 'status', 'workflow_request_id', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function tasks()
    {
        return $this->hasMany(OnboardingTaskModel::class, 'onboarding_plan_id');
    }
}
