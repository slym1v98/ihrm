<?php

namespace App\Modules\Training\Domain\Repositories;

use App\Modules\Training\Domain\Aggregates\TrainingResult\TrainingResult;
use App\Modules\Training\Domain\Aggregates\TrainingResult\TrainingResultId;

interface TrainingResultRepositoryInterface
{
    public function findById(TrainingResultId $id): ?TrainingResult;

    public function findByEnrollmentId(string $enrollmentId): ?TrainingResult;

    public function save(TrainingResult $result): void;
}
