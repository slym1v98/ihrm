<?php

namespace App\Modules\Training\Application\CommandHandlers;

use App\Modules\Training\Application\Commands\CreateSessionCommand;
use App\Modules\Training\Domain\Aggregates\TrainingSession\TrainingSession;
use App\Modules\Training\Domain\Aggregates\TrainingSession\TrainingSessionId;
use App\Modules\Training\Domain\Repositories\TrainingSessionRepositoryInterface;

class CreateSessionHandler
{
    public function __construct(private readonly TrainingSessionRepositoryInterface $repo) {}

    public function handle(CreateSessionCommand $cmd): TrainingSession
    {
        $s = TrainingSession::schedule(TrainingSessionId::generate(), $cmd->courseId, $cmd->code, $cmd->name, new \DateTimeImmutable($cmd->startDate), new \DateTimeImmutable($cmd->endDate), $cmd->location, $cmd->instructor, $cmd->maxParticipants);
        $this->repo->save($s);

        return $s;
    }
}
