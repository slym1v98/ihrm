<?php

namespace App\Modules\Onboarding\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class OnboardingTaskModel extends Model
{
    use HasUuids;

    protected $table = 'onboarding_tasks';

    protected $fillable = [
        'id', 'onboarding_plan_id', 'task_type', 'owner_type', 'owner_id',
        'title', 'description', 'due_date', 'status', 'requires_approval',
        'approval_workflow_request_id', 'proof_file_object_id', 'sort_order', 'is_pre_start',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'requires_approval' => 'boolean',
            'is_pre_start' => 'boolean',
        ];
    }
}
