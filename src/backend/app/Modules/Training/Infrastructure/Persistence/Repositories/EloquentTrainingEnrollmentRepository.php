<?php
namespace App\Modules\Training\Infrastructure\Persistence\Repositories;
use App\Modules\Training\Domain\Aggregates\TrainingEnrollment\TrainingEnrollment;
use App\Modules\Training\Domain\Aggregates\TrainingEnrollment\TrainingEnrollmentId;
use App\Modules\Training\Domain\Repositories\TrainingEnrollmentRepositoryInterface;
use App\Modules\Training\Domain\ValueObjects\EnrollmentStatus;
use App\Modules\Training\Infrastructure\Persistence\Eloquent\TrainingEnrollmentModel;
class EloquentTrainingEnrollmentRepository implements TrainingEnrollmentRepositoryInterface {
    public function findById(TrainingEnrollmentId $id): ?TrainingEnrollment { $m = TrainingEnrollmentModel::find($id->value); return $m ? $this->toDomain($m) : null; }
    public function findBySessionId(string $sessionId): array { return TrainingEnrollmentModel::where('session_id',$sessionId)->get()->map(fn($m)=>$this->toDomain($m))->toArray(); }
    public function findByEmployeeAndSession(string $sessionId, string $employeeId): ?TrainingEnrollment { $m = TrainingEnrollmentModel::where('session_id',$sessionId)->where('employee_id',$employeeId)->first(); return $m ? $this->toDomain($m) : null; }
    public function save(TrainingEnrollment $e): void { TrainingEnrollmentModel::updateOrCreate(['id'=>$e->getId()->value], ['session_id'=>$e->getSessionId(),'employee_id'=>$e->getEmployeeId(),'enrolled_at'=>$e->getEnrolledAt()->format('Y-m-d H:i:s'),'attendance'=>$e->getAttendance(),'status'=>$e->getStatus()->value]); }
    private function toDomain(TrainingEnrollmentModel $m): TrainingEnrollment { return TrainingEnrollment::reconstitute(TrainingEnrollmentId::fromString($m->id),$m->session_id,$m->employee_id,new \DateTimeImmutable($m->enrolled_at),$m->attendance,EnrollmentStatus::from($m->status)); }
}
