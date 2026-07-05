<?php

namespace App\Modules\Organization\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BranchModel extends Model
{
    use HasUuids;

    protected $table = 'branches';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    public function departments(): HasMany
    {
        return $this->hasMany(DepartmentModel::class, 'branch_id');
    }

    public function activeDepartments(): HasMany
    {
        return $this->hasMany(DepartmentModel::class, 'branch_id')->where('status', 'active');
    }
}
