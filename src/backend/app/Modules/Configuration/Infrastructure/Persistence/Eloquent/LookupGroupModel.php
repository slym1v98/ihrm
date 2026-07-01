<?php

namespace App\Modules\Configuration\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LookupGroupModel extends Model
{
    use HasUuids;

    protected $table = 'lookup_groups';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
    protected $casts = ['active' => 'boolean'];

    public function values(): HasMany
    {
        return $this->hasMany(LookupValueModel::class, 'group_id')->orderBy('sort_order')->orderBy('name');
    }
}
