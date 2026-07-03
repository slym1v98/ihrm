<?php
namespace App\Modules\Training\Infrastructure\Persistence\Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
class TrainingEnrollmentModel extends Model { use HasUuids; protected $table = 'training_enrollments'; protected $fillable = ['id','session_id','employee_id','enrolled_at','attendance','status']; protected function casts(): array { return ['enrolled_at'=>'datetime','attendance'=>'array']; } }
