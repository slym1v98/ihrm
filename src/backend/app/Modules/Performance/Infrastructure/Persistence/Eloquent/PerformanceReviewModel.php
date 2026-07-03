<?php

namespace App\Modules\Performance\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PerformanceReviewModel extends Model
{
    use HasUuids;
    protected $table = 'performance_reviews';
    protected $fillable = ['id', 'cycle_id', 'employee_id', 'self_assessment', 'manager_assessment', 'hr_assessment', 'final_score', 'status', 'finalized_at'];
    protected function casts(): array { return ['self_assessment' => 'array', 'manager_assessment' => 'array', 'hr_assessment' => 'array', 'final_score' => 'decimal:2', 'finalized_at' => 'datetime']; }
}
