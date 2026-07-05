<?php

namespace App\Modules\Recruitment\Domain\Repositories;

use App\Modules\Recruitment\Domain\Aggregates\Interview\Interview;
use App\Modules\Recruitment\Domain\Aggregates\Interview\InterviewId;

interface InterviewRepositoryInterface
{
    public function findById(InterviewId $id): ?Interview;

    /** @return Interview[] */
    public function list(?string $candidateId = null): array;

    public function save(Interview $i): void;
}
