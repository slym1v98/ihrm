<?php
namespace App\Modules\Recruitment\Application\CommandHandlers;
use App\Modules\Recruitment\Application\Commands\AddCandidateCommand;
use App\Modules\Recruitment\Domain\Aggregates\Candidate\{Candidate,CandidateId};
use App\Modules\Recruitment\Domain\Exceptions\DuplicateCandidateException;
use App\Modules\Recruitment\Domain\Repositories\CandidateRepositoryInterface;
use App\Modules\Recruitment\Domain\ValueObjects\CandidateSource;
class AddCandidateHandler {
    public function __construct(private CandidateRepositoryInterface $repo) {}
    public function handle(AddCandidateCommand $cmd): Candidate {
        if($cmd->email && $this->repo->findByEmail($cmd->email)) throw new DuplicateCandidateException('email:'.$cmd->email);
        if($cmd->phone && $this->repo->findByPhone($cmd->phone)) throw new DuplicateCandidateException('phone:'.$cmd->phone);
        $c=Candidate::create(CandidateId::generate(),$cmd->requisitionId,$cmd->fullName,$cmd->email,$cmd->phone,CandidateSource::from($cmd->source),$cmd->cvFileDescriptor,$cmd->notes);
        $this->repo->save($c); return $c;
    }
}
