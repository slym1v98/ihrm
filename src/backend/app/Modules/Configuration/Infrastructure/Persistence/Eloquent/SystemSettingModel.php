<?php

namespace App\Modules\Configuration\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SystemSettingModel extends Model
{
    use HasUuids;

    protected $table = 'system_settings';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = ['editable' => 'boolean'];
}
