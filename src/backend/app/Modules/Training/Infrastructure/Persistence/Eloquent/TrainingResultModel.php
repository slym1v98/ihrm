<?php
namespace App\Modules\Training\Infrastructure\Persistence\Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
class TrainingResultModel extends Model { use HasUuids; protected $table = 'training_results'; protected $fillable = ['id','enrollment_id','score','passed','certificate_code','issued_at','notes']; protected function casts(): array { return ['score'=>'decimal:2','passed'=>'boolean','issued_at'=>'datetime']; } }
