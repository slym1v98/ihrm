<?php

namespace App\Modules\Recruitment\Infrastructure\Persistence\Repositories;

use App\Modules\Recruitment\Domain\Aggregates\Candidate\Candidate;
use App\Modules\Recruitment\Domain\Aggregates\Candidate\CandidateId;
use App\Modules\Recruitment\Domain\Repositories\CandidateRepositoryInterface;
use App\Modules\Recruitment\Domain\ValueObjects\CandidateSource;
use App\Modules\Recruitment\Domain\ValueObjects\CandidateStatus;
use App\Modules\Recruitment\Infrastructure\Persistence\Eloquent\CandidateModel;

class EloquentCandidateRepository implements CandidateRepositoryInterface
{
    public function __construct(private CandidateModel $model) {}

    public function findById(CandidateId $id): ?Candidate
    {
        $r = $this->model->find($id->value());

        return $r ? self::toDomain($r) : null;
    }

    public function findByEmail(string $email): ?Candidate
    {
        $r = $this->model->where('email', $email)->first();

        return $r ? self::toDomain($r) : null;
    }

    public function findByPhone(string $phone): ?Candidate
    {
        $r = $this->model->where('phone', $phone)->first();

        return $r ? self::toDomain($r) : null;
    }

    public function list(?string $requisitionId = null): array
    {
        $q = $this->model->orderByDesc('created_at');
        if ($requisitionId) {
            $q->where('requisition_id', $requisitionId);
        }

        return $q->get()->map(fn ($r) => self::toDomain($r))->all();
    }

    public function save(Candidate $c): void
    {
        $this->model->updateOrCreate(['id' => (string) $c->getId()], [
            'requisition_id' => $c->getRequisitionId(),
            'employee_id' => $c->getEmployeeId(),
            'full_name' => $c->getFullName(),
            'email' => $c->getEmail(),
            'phone' => $c->getPhone(),
            'source' => $c->getSource()->value,
            'cv_file_descriptor' => $c->getCvFileDescriptor(),
            'status' => $c->getStatus()->value,
            'notes' => $c->getNotes(),
        ]);
    }

    public static function toDomain(CandidateModel $m): Candidate
    {
        return Candidate::reconstitute(new CandidateId($m->id), $m->requisition_id, $m->employee_id, $m->full_name, $m->email, $m->phone, CandidateSource::from($m->source), $m->cv_file_descriptor, CandidateStatus::from($m->status), $m->notes);
    }
}
