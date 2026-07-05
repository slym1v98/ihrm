<?php

namespace App\Modules\Performance\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PerformanceCycleModel extends Model
{
    use HasUuids;

    protected $table = 'performance_cycles';

    protected $fillable = ['id', 'code', 'name', 'description', 'start_date', 'end_date', 'status', 'scoring_rules', 'workflow_request_id'];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date', 'scoring_rules' => 'array'];
    }
}
