<?php

namespace App\Modules\Configuration\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LookupValueModel extends Model
{
    use HasUuids;

    protected $table = 'lookup_values';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
    protected $casts = ['active' => 'boolean', 'metadata' => 'array'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(LookupGroupModel::class, 'group_id');
    }
}
