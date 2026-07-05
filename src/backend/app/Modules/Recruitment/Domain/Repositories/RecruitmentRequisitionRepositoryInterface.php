<?php

namespace App\Modules\Recruitment\Domain\Repositories;

use App\Modules\Recruitment\Domain\Aggregates\RecruitmentRequisition\RecruitmentRequisition;
use App\Modules\Recruitment\Domain\Aggregates\RecruitmentRequisition\RecruitmentRequisitionId;

interface RecruitmentRequisitionRepositoryInterface
{
    public function findById(RecruitmentRequisitionId $id): ?RecruitmentRequisition;

    /** @return RecruitmentRequisition[] */
    public function list(): array;

    public function findByWorkflowRequestId(string $wflId): ?RecruitmentRequisition;

    public function save(RecruitmentRequisition $req): void;
}
