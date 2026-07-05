<?php

namespace App\Modules\Workflow\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WorkflowRequestModel extends Model
{
    use HasUuids;

    protected $table = 'workflow_requests';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['id', 'workflow_template_id', 'subject_type', 'subject_id', 'status', 'current_step', 'submitted_by', 'context', 'sla_deadline_at', 'escalated', 'parallel_approved_count', 'parallel_required_count'];

    protected $casts = [
        'current_step' => 'integer',
        'context' => 'array',
        'sla_deadline_at' => 'datetime',
        'escalated' => 'boolean',
        'parallel_approved_count' => 'integer',
        'parallel_required_count' => 'integer',
    ];

    public function actions()
    {
        return $this->hasMany(WorkflowRequestActionModel::class, 'workflow_request_id')->orderBy('created_at');
    }
}
