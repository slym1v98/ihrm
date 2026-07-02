<?php
namespace App\Modules\Recruitment\Application\CommandHandlers;
use App\Modules\Recruitment\Application\Commands\SubmitScorecardCommand;
use App\Modules\Recruitment\Domain\Aggregates\Interview\InterviewId;
use App\Modules\Recruitment\Domain\Exceptions\InterviewNotFoundException;
use App\Modules\Recruitment\Domain\Repositories\InterviewRepositoryInterface;
use Carbon\CarbonImmutable;
class SubmitScorecardHandler {
    public function __construct(private InterviewRepositoryInterface $repo) {}
    public function handle(SubmitScorecardCommand $cmd): void {
        $i=$this->repo->findById(new InterviewId($cmd->interviewId));
        if(!$i) throw new InterviewNotFoundException($cmd->interviewId);
        $i->submitScorecard($cmd->interviewerId,$cmd->score,$cmd->comment,CarbonImmutable::now());
        $this->repo->save($i);
    }
}
