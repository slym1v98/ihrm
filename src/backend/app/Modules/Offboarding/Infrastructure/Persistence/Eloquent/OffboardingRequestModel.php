<?php

namespace App\Modules\Offboarding\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class OffboardingRequestModel extends Model
{
    use HasUuids;
    protected $table = 'offboarding_requests';
    protected $fillable = ['id', 'employee_id', 'type', 'reason', 'requested_last_working_date', 'approved_last_working_date', 'status', 'workflow_request_id'];
    protected function casts(): array { return ['requested_last_working_date' => 'date', 'approved_last_working_date' => 'date']; }
}
