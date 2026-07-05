<?php

namespace App\Modules\Performance\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CompetencyTemplateModel extends Model
{
    use HasUuids;

    protected $table = 'competency_templates';

    protected $fillable = ['id', 'code', 'name', 'rules', 'active'];

    protected function casts(): array
    {
        return ['rules' => 'array', 'active' => 'boolean'];
    }
}
