<?php

namespace App\Modules\Employee\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class EmployeeDocumentModel extends Model
{
    use HasUuids;

    protected $table = 'employee_documents';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'expiry_date' => 'date',
            'file_size' => 'integer',
        ];
    }
}
