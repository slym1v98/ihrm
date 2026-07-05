<?php

namespace App\Modules\Training\Application\CommandHandlers;

use App\Modules\Training\Application\Commands\CompleteEnrollmentCommand;
use App\Modules\Training\Domain\Aggregates\TrainingEnrollment\TrainingEnrollmentId;
use App\Modules\Training\Domain\Exceptions\TrainingEnrollmentNotFoundException;
use App\Modules\Training\Domain\Repositories\TrainingEnrollmentRepositoryInterface;

class CompleteEnrollmentHandler
{
    public function __construct(private readonly TrainingEnrollmentRepositoryInterface $repo) {}

    public function handle(CompleteEnrollmentCommand $cmd): void
    {
        $e = $this->repo->findById(TrainingEnrollmentId::fromString($cmd->id)) ?? throw new TrainingEnrollmentNotFoundException($cmd->id);
        $e->complete();
        $this->repo->save($e);
    }
}
