<?php

namespace App\Modules\Recruitment\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CandidateModel extends Model
{
    use HasUuids;

    protected $table = 'recruitment_candidates';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['id', 'requisition_id', 'employee_id', 'full_name', 'email', 'phone', 'source', 'cv_file_descriptor', 'status', 'notes'];
}
