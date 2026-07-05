<?php

namespace App\Modules\Training\Infrastructure\Persistence\Repositories;

use App\Modules\Training\Domain\Aggregates\TrainingSession\TrainingSession;
use App\Modules\Training\Domain\Aggregates\TrainingSession\TrainingSessionId;
use App\Modules\Training\Domain\Repositories\TrainingSessionRepositoryInterface;
use App\Modules\Training\Domain\ValueObjects\SessionStatus;
use App\Modules\Training\Infrastructure\Persistence\Eloquent\TrainingEnrollmentModel;
use App\Modules\Training\Infrastructure\Persistence\Eloquent\TrainingSessionModel;

class EloquentTrainingSessionRepository implements TrainingSessionRepositoryInterface
{
    public function findById(TrainingSessionId $id): ?TrainingSession
    {
        $m = TrainingSessionModel::find($id->value);

        return $m ? $this->toDomain($m) : null;
    }

    public function findByCourseId(string $courseId): array
    {
        return TrainingSessionModel::where('course_id', $courseId)->orderBy('start_date')->get()->map(fn ($m) => $this->toDomain($m))->toArray();
    }

    public function countEnrolled(string $sessionId): int
    {
        return TrainingEnrollmentModel::where('session_id', $sessionId)->where('status', '!=', 'cancelled')->count();
    }

    public function save(TrainingSession $s): void
    {
        TrainingSessionModel::updateOrCreate(['id' => $s->getId()->value], ['course_id' => $s->getCourseId(), 'code' => $s->getCode(), 'name' => $s->getName(), 'start_date' => $s->getStartDate()->format('Y-m-d H:i:s'), 'end_date' => $s->getEndDate()->format('Y-m-d H:i:s'), 'location' => $s->getLocation(), 'instructor' => $s->getInstructor(), 'max_participants' => $s->getMaxParticipants(), 'status' => $s->getStatus()->value]);
    }

    private function toDomain(TrainingSessionModel $m): TrainingSession
    {
        return TrainingSession::reconstitute(TrainingSessionId::fromString($m->id), $m->course_id, $m->code, $m->name, new \DateTimeImmutable($m->start_date), new \DateTimeImmutable($m->end_date), $m->location, $m->instructor, $m->max_participants, SessionStatus::from($m->status));
    }
}
