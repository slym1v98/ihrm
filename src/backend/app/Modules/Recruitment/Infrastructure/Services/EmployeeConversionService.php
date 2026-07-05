<?php

namespace App\Modules\Recruitment\Infrastructure\Services;

use App\Modules\Recruitment\Domain\Aggregates\Candidate\Candidate;
use App\Modules\Recruitment\Domain\Aggregates\RecruitmentRequisition\RecruitmentRequisition;
use Ramsey\Uuid\Uuid;

class EmployeeConversionService
{
    public function convert(Candidate $candidate, RecruitmentRequisition $requisition): string
    {
        return Uuid::uuid7()->toString();
    }
}
