<?php

namespace App\Modules\Performance\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CompetencyTemplateModel extends Model
{
    use HasUuids;
    protected $table = 'competency_templates';
    protected $fillable = ['id', 'code', 'name', 'rules', 'active'];
    protected function casts(): array { return ['rules' => 'array', 'active' => 'boolean']; }
}
