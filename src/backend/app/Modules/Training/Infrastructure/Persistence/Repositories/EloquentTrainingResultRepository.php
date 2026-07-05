<?php

namespace App\Modules\Training\Infrastructure\Persistence\Repositories;

use App\Modules\Training\Domain\Aggregates\TrainingResult\TrainingResult;
use App\Modules\Training\Domain\Aggregates\TrainingResult\TrainingResultId;
use App\Modules\Training\Domain\Repositories\TrainingResultRepositoryInterface;
use App\Modules\Training\Infrastructure\Persistence\Eloquent\TrainingResultModel;

class EloquentTrainingResultRepository implements TrainingResultRepositoryInterface
{
    public function findById(TrainingResultId $id): ?TrainingResult
    {
        $m = TrainingResultModel::find($id->value);

        return $m ? $this->toDomain($m) : null;
    }

    public function findByEnrollmentId(string $enrollmentId): ?TrainingResult
    {
        $m = TrainingResultModel::where('enrollment_id', $enrollmentId)->first();

        return $m ? $this->toDomain($m) : null;
    }

    public function save(TrainingResult $r): void
    {
        TrainingResultModel::updateOrCreate(['id' => $r->getId()->value], ['enrollment_id' => $r->getEnrollmentId(), 'score' => $r->getScore(), 'passed' => $r->getPassed(), 'certificate_code' => $r->getCertificateCode(), 'issued_at' => $r->getIssuedAt()?->format('Y-m-d H:i:s'), 'notes' => $r->getNotes()]);
    }

    private function toDomain(TrainingResultModel $m): TrainingResult
    {
        return TrainingResult::reconstitute(TrainingResultId::fromString($m->id), $m->enrollment_id, $m->score !== null ? (float) $m->score : null, $m->passed !== null ? (bool) $m->passed : null, $m->certificate_code, $m->issued_at ? new \DateTimeImmutable($m->issued_at) : null, $m->notes);
    }
}
