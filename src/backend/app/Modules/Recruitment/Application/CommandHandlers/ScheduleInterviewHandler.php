<?php

namespace App\Modules\Recruitment\Application\CommandHandlers;

use App\Modules\Recruitment\Application\Commands\ScheduleInterviewCommand;
use App\Modules\Recruitment\Domain\Aggregates\Interview\Interview;
use App\Modules\Recruitment\Domain\Aggregates\Interview\InterviewId;
use App\Modules\Recruitment\Domain\Repositories\InterviewRepositoryInterface;
use Carbon\CarbonImmutable;

class ScheduleInterviewHandler
{
    public function __construct(private InterviewRepositoryInterface $repo) {}

    public function handle(ScheduleInterviewCommand $cmd): Interview
    {
        $i = Interview::schedule(InterviewId::generate(), $cmd->candidateId, $cmd->requisitionId, $cmd->interviewers, CarbonImmutable::parse($cmd->scheduledAt), $cmd->notes);
        $this->repo->save($i);

        return $i;
    }
}
