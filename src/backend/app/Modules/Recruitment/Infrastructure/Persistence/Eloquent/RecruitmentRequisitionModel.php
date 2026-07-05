<?php

namespace App\Modules\Recruitment\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RecruitmentRequisitionModel extends Model
{
    use HasUuids;
    protected $table = 'recruitment_requisitions';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['id','department_id','position','headcount','reason','status','workflow_request_id','opened_at','closed_at','created_by'];
    protected $casts = ['headcount'=>'integer','opened_at'=>'datetime','closed_at'=>'datetime'];
}
