<?php

namespace App\Modules\Workflow\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WorkflowDelegationModel extends Model
{
    use HasUuids;

    protected $table = 'workflow_delegations';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'delegator_id',
        'delegate_id',
        'role_type',
        'start_at',
        'end_at',
        'active',
        'created_by',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'active' => 'boolean',
    ];
}
