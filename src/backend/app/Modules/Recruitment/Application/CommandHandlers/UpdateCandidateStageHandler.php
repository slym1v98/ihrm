<?php
namespace App\Modules\Recruitment\Application\CommandHandlers;
use App\Modules\Recruitment\Application\Commands\UpdateCandidateStageCommand;
use App\Modules\Recruitment\Domain\Aggregates\Candidate\CandidateId;
use App\Modules\Recruitment\Domain\Exceptions\CandidateNotFoundException;
use App\Modules\Recruitment\Domain\Repositories\CandidateRepositoryInterface;
use App\Modules\Recruitment\Domain\ValueObjects\CandidateStatus;
class UpdateCandidateStageHandler {
    public function __construct(private CandidateRepositoryInterface $repo) {}
    public function handle(UpdateCandidateStageCommand $cmd): void {
        $c=$this->repo->findById(new CandidateId($cmd->id));
        if(!$c) throw new CandidateNotFoundException($cmd->id);
        $c->moveTo(CandidateStatus::from($cmd->status)); $this->repo->save($c);
    }
}
