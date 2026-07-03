<?php
namespace App\Modules\Training\Infrastructure\Persistence\Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
class TrainingSessionModel extends Model { use HasUuids; protected $table = 'training_sessions'; protected $fillable = ['id','course_id','code','name','start_date','end_date','location','instructor','max_participants','status']; protected function casts(): array { return ['start_date'=>'datetime','end_date'=>'datetime']; } }
