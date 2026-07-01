<?php

namespace App\Modules\Employee\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class EmployeeModel extends Model
{
    use HasUuids;

    protected $table = 'employees';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
        ];
    }
}
