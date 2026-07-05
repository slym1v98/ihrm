<?php

namespace App\Modules\Training\Application\CommandHandlers;

use App\Modules\Training\Application\Commands\RecordResultCommand;
use App\Modules\Training\Domain\Aggregates\TrainingResult\TrainingResult;
use App\Modules\Training\Domain\Aggregates\TrainingResult\TrainingResultId;
use App\Modules\Training\Domain\Repositories\TrainingResultRepositoryInterface;

class RecordResultHandler
{
    public function __construct(private readonly TrainingResultRepositoryInterface $repo) {}

    public function handle(RecordResultCommand $cmd): TrainingResult
    {
        if ($this->repo->findByEnrollmentId($cmd->enrollmentId)) {
            throw new \RuntimeException('Result already exists');
        } $r = TrainingResult::record(TrainingResultId::generate(), $cmd->enrollmentId, $cmd->score, $cmd->passed, $cmd->certificateCode, $cmd->notes);
        $this->repo->save($r);

        return $r;
    }
}
