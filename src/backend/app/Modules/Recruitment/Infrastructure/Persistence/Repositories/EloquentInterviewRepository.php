<?php

namespace App\Modules\Recruitment\Infrastructure\Persistence\Repositories;

use App\Modules\Recruitment\Domain\Aggregates\Interview\Interview;
use App\Modules\Recruitment\Domain\Aggregates\Interview\InterviewId;
use App\Modules\Recruitment\Domain\Repositories\InterviewRepositoryInterface;
use App\Modules\Recruitment\Domain\ValueObjects\InterviewStatus;
use App\Modules\Recruitment\Infrastructure\Persistence\Eloquent\InterviewModel;
use Carbon\CarbonImmutable;

class EloquentInterviewRepository implements InterviewRepositoryInterface
{
    public function __construct(private InterviewModel $model) {}

    public function findById(InterviewId $id): ?Interview
    {
        $r = $this->model->find($id->value());

        return $r ? self::toDomain($r) : null;
    }

    public function list(?string $candidateId = null): array
    {
        $q = $this->model->orderByDesc('scheduled_at');
        if ($candidateId) {
            $q->where('candidate_id', $candidateId);
        }

        return $q->get()->map(fn ($r) => self::toDomain($r))->all();
    }

    public function save(Interview $i): void
    {
        $this->model->updateOrCreate(['id' => (string) $i->getId()], [
            'candidate_id' => $i->getCandidateId(),
            'requisition_id' => $i->getRequisitionId(),
            'interviewers' => $i->getInterviewers(),
            'scheduled_at' => $i->getScheduledAt()->toDateTimeString(),
            'status' => $i->getStatus()->value,
            'scorecards' => $i->getScorecards(),
            'notes' => $i->getNotes(),
        ]);
    }

    public static function toDomain(InterviewModel $m): Interview
    {
        return Interview::reconstitute(new InterviewId($m->id), $m->candidate_id, $m->requisition_id, $m->interviewers ?? [], CarbonImmutable::parse($m->scheduled_at), InterviewStatus::from($m->status), $m->scorecards ?? [], $m->notes);
    }
}
