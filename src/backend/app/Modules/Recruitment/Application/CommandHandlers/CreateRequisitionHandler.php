<?php
namespace App\Modules\Recruitment\Application\CommandHandlers;
use App\Modules\Recruitment\Application\Commands\CreateRequisitionCommand;
use App\Modules\Recruitment\Domain\Aggregates\RecruitmentRequisition\{RecruitmentRequisition,RecruitmentRequisitionId};
use App\Modules\Recruitment\Domain\Repositories\RecruitmentRequisitionRepositoryInterface;
class CreateRequisitionHandler {
    public function __construct(private RecruitmentRequisitionRepositoryInterface $repo) {}
    public function handle(CreateRequisitionCommand $cmd): RecruitmentRequisition {
        $req=RecruitmentRequisition::create(RecruitmentRequisitionId::generate(),$cmd->departmentId,$cmd->position,$cmd->headcount,$cmd->reason,$cmd->createdBy);
        $this->repo->save($req); return $req;
    }
}
