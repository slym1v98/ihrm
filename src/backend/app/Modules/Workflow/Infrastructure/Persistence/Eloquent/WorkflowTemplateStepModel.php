<?php

namespace App\Modules\Workflow\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

class WorkflowTemplateStepModel extends Model
{
    protected $table = 'workflow_template_steps';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'workflow_template_id', 'step_order', 'name', 'assignee_type', 'assignee_id', 'condition', 'resolver_type', 'resolver_config'];

    protected $casts = [
        'step_order' => 'integer',
        'condition' => 'array',
        'resolver_config' => 'array',
    ];
}
