<?php

namespace App\Modules\Employee\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ContractModel extends Model
{
    use HasUuids;

    protected $table = 'employee_contracts';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'sign_date' => 'date',
            'base_salary' => 'decimal:2',
        ];
    }
}
