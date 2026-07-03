<?php

namespace App\Modules\Offboarding\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class OffboardingTaskModel extends Model
{
    use HasUuids;
    protected $table = 'offboarding_tasks';
    protected $fillable = ['id', 'offboarding_plan_id', 'task_type', 'owner_type', 'owner_id', 'title', 'description', 'due_date', 'status', 'requires_approval', 'approval_workflow_request_id', 'proof_file_object_id', 'sort_order'];
    protected function casts(): array { return ['due_date' => 'date', 'requires_approval' => 'boolean']; }
}
