<?php

namespace App\Modules\Configuration\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CodeGenerationRuleModel extends Model
{
    use HasUuids;

    protected $table = 'code_generation_rules';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = ['active' => 'boolean', 'next_number' => 'integer', 'sequence_padding' => 'integer'];
}
