<?php
namespace App\Modules\Recruitment\Infrastructure\Persistence\Repositories;
use App\Modules\Recruitment\Domain\Aggregates\RecruitmentRequisition\RecruitmentRequisition;
use App\Modules\Recruitment\Domain\Aggregates\RecruitmentRequisition\RecruitmentRequisitionId;
use App\Modules\Recruitment\Domain\Repositories\RecruitmentRequisitionRepositoryInterface;
use App\Modules\Recruitment\Domain\ValueObjects\RequisitionStatus;
use App\Modules\Recruitment\Infrastructure\Persistence\Eloquent\RecruitmentRequisitionModel;
use Carbon\CarbonImmutable;

class EloquentRecruitmentRequisitionRepository implements RecruitmentRequisitionRepositoryInterface {
    public function __construct(private RecruitmentRequisitionModel $model) {}
    public function findById(RecruitmentRequisitionId $id): ?RecruitmentRequisition { $r=$this->model->find($id->value()); return $r?self::toDomain($r):null; }
    public function findByWorkflowRequestId(string $wflId): ?RecruitmentRequisition { $r=$this->model->where('workflow_request_id',$wflId)->first(); return $r?self::toDomain($r):null; }
    public function list(): array { return $this->model->orderByDesc('created_at')->get()->map(fn($r)=>self::toDomain($r))->all(); }
    public function save(RecruitmentRequisition $req): void {
        $this->model->updateOrCreate(['id'=>(string)$req->getId()],[
            'department_id'=>$req->getDepartmentId(),
            'position'=>$req->getPosition(),
            'headcount'=>$req->getHeadcount(),
            'reason'=>$req->getReason(),
            'status'=>$req->getStatus()->value,
            'workflow_request_id'=>$req->getWorkflowRequestId(),
            'opened_at'=>$req->getOpenedAt()?->toDateTimeString(),
            'closed_at'=>$req->getClosedAt()?->toDateTimeString(),
            'created_by'=>$req->getCreatedBy(),
        ]);
    }
    public static function toDomain(RecruitmentRequisitionModel $m): RecruitmentRequisition {
        return RecruitmentRequisition::reconstitute(
            new RecruitmentRequisitionId($m->id),$m->department_id,$m->position,$m->headcount,$m->reason,
            RequisitionStatus::from($m->status),$m->workflow_request_id,
            $m->opened_at?CarbonImmutable::parse($m->opened_at):null,
            $m->closed_at?CarbonImmutable::parse($m->closed_at):null,
            $m->created_by,
        );
    }
}
