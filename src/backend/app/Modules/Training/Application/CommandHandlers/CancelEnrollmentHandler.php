<?php

namespace App\Modules\Training\Application\CommandHandlers;

use App\Modules\Training\Application\Commands\CancelEnrollmentCommand;
use App\Modules\Training\Domain\Aggregates\TrainingEnrollment\TrainingEnrollmentId;
use App\Modules\Training\Domain\Exceptions\TrainingEnrollmentNotFoundException;
use App\Modules\Training\Domain\Repositories\TrainingEnrollmentRepositoryInterface;

class CancelEnrollmentHandler
{
    public function __construct(private readonly TrainingEnrollmentRepositoryInterface $repo) {}

    public function handle(CancelEnrollmentCommand $cmd): void
    {
        $e = $this->repo->findById(TrainingEnrollmentId::fromString($cmd->id)) ?? throw new TrainingEnrollmentNotFoundException($cmd->id);
        $e->cancel();
        $this->repo->save($e);
    }
}
