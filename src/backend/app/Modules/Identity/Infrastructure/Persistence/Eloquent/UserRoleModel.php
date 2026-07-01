<?php

namespace App\Modules\Identity\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class UserRoleModel extends Model
{
    use HasUuids;

    protected $table = 'user_roles';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
    protected $casts = [
        'assigned_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];
}
