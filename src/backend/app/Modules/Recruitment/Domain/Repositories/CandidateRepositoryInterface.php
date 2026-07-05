<?php

namespace App\Modules\Recruitment\Domain\Repositories;

use App\Modules\Recruitment\Domain\Aggregates\Candidate\Candidate;
use App\Modules\Recruitment\Domain\Aggregates\Candidate\CandidateId;

interface CandidateRepositoryInterface
{
    public function findById(CandidateId $id): ?Candidate;

    public function findByEmail(string $email): ?Candidate;

    public function findByPhone(string $phone): ?Candidate;

    /** @return Candidate[] */
    public function list(?string $requisitionId = null): array;

    public function save(Candidate $c): void;
}
