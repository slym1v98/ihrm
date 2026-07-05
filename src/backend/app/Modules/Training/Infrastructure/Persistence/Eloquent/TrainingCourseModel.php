<?php

namespace App\Modules\Training\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TrainingCourseModel extends Model
{
    use HasUuids;

    protected $table = 'training_courses';

    protected $fillable = ['id', 'code', 'name', 'description', 'category', 'default_duration_hours', 'max_participants', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }
}
