<?php

namespace App\Modules\Recruitment\Application\CommandHandlers;

use App\Modules\Recruitment\Application\Commands\SubmitRequisitionCommand;
use App\Modules\Recruitment\Domain\Aggregates\RecruitmentRequisition\RecruitmentRequisitionId;
use App\Modules\Recruitment\Domain\Exceptions\RecruitmentRequisitionNotFoundException;
use App\Modules\Recruitment\Domain\Repositories\RecruitmentRequisitionRepositoryInterface;
use App\Modules\Recruitment\Infrastructure\Services\WorkflowIntegrationService;

class SubmitRequisitionHandler
{
    public function __construct(private RecruitmentRequisitionRepositoryInterface $repo, private WorkflowIntegrationService $workflow) {}

    public function handle(SubmitRequisitionCommand $cmd): void
    {
        $req = $this->repo->findById(new RecruitmentRequisitionId($cmd->id));
        if (! $req) {
            throw new RecruitmentRequisitionNotFoundException($cmd->id);
        }
        $wflId = $this->workflow->startRequisitionApproval($cmd->id, $cmd->submittedBy);
        $req->submit($wflId);
        $this->repo->save($req);
    }
}
