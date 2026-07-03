<?php

namespace App\Modules\Performance\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class GoalModel extends Model
{
    use HasUuids;
    protected $table = 'goals';
    protected $fillable = ['id', 'cycle_id', 'employee_id', 'title', 'description', 'weight', 'target_value', 'actual_value', 'status', 'sort_order'];
    protected function casts(): array { return ['weight' => 'decimal:2']; }
}
